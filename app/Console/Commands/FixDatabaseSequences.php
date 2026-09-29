<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * PostgreSQL only. When rows are inserted with explicit ids (data copied
 * from another database, manual SQL, imports), a table's id sequence is
 * not moved on, and the next insert fails with
 *   "duplicate key value violates unique constraint "users_pkey""
 * This moves every such sequence to the table's highest id. It never
 * lowers a sequence and never changes any data rows.
 */
class FixDatabaseSequences extends Command
{
    protected $signature = 'db:fix-sequences {--dry-run : Only show which sequences are behind}';

    protected $description = 'PostgreSQL: move id sequences that are behind the highest id (fixes "duplicate key ... _pkey" errors)';

    public function handle()
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->info('Nothing to do: this database is ' . DB::getDriverName() . ' (only PostgreSQL has this problem).');

            return self::SUCCESS;
        }

        // Every table in the current schema whose "id" column is backed by a sequence.
        $tables = DB::select("
            SELECT c.table_name AS table_name, pg_get_serial_sequence(quote_ident(c.table_name), 'id') AS seq
            FROM information_schema.columns c
            JOIN information_schema.tables t ON t.table_name = c.table_name AND t.table_schema = c.table_schema
            WHERE c.table_schema = current_schema() AND c.column_name = 'id' AND t.table_type = 'BASE TABLE'
            ORDER BY c.table_name
        ");

        $rows = [];
        $fixed = 0;
        foreach ($tables as $t) {
            if (!$t->seq) {
                continue;
            }

            $max = (int) DB::table($t->table_name)->max('id');
            $state = DB::selectOne('SELECT last_value, is_called FROM ' . $t->seq);
            // Next id the sequence would hand out.
            $next = $state->is_called ? (int) $state->last_value + 1 : (int) $state->last_value;

            if ($max < $next) {
                continue; // fine
            }

            $rows[] = [$t->table_name, $max, $next, $max + 1];
            if (!$this->option('dry-run')) {
                DB::select('SELECT setval(?, ?, true)', [$t->seq, $max]);
                $fixed++;
            }
        }

        if (!$rows) {
            $this->info('All id sequences are fine.');

            return self::SUCCESS;
        }

        $this->table(['Table', 'Highest id', 'Sequence would give', 'Will give'], $rows);
        $this->info($this->option('dry-run')
            ? count($rows) . ' table(s) are behind. Run without --dry-run to fix them.'
            : "Fixed {$fixed} table(s). New records will get the next free id.");

        return self::SUCCESS;
    }
}
