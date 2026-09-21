<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceDataSeeder extends Seeder
{
    /**
     * Populates the tables that are pure reference/lookup data (Tanzania
     * geography + church/member designations + permission catalogue) -
     * data the UI depends on to function, as opposed to operational
     * records (churches, members, companies, etc.) which belong to the
     * live database and are migrated separately, not seeded here.
     */
    public function run()
    {
        $this->insertWithIds('regions', require __DIR__.'/data/regions.php');
        $this->insertWithIds('districts', require __DIR__.'/data/districts.php');

        foreach (array_chunk(require __DIR__.'/data/wards.php', 500) as $chunk) {
            $this->insertWithIds('wards', $chunk, false);
        }
        $this->resyncSequence('wards');

        $now = now();

        $this->insertWithIds('church_designations', $this->withTimestamps(
            require __DIR__.'/data/church_designations.php', $now
        ));

        $this->insertWithIds('member_designations', $this->withTimestamps(
            require __DIR__.'/data/member_designations.php', $now
        ));

        $this->insertWithIds('permissions', $this->withTimestamps(
            require __DIR__.'/data/permissions.php', $now
        ));

        $this->insertWithIds('role_permissions', require __DIR__.'/data/role_permissions.php');
    }

    private function withTimestamps(array $rows, $now): array
    {
        return array_map(function ($row) use ($now) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;

            return $row;
        }, $rows);
    }

    private function insertWithIds(string $table, array $rows, bool $resync = true): void
    {
        if (empty($rows)) {
            return;
        }

        $existingIds = DB::table($table)->pluck('id')->flip();

        $rows = array_values(array_filter($rows, function ($row) use ($existingIds) {
            return !$existingIds->has($row['id']);
        }));

        if (!empty($rows)) {
            DB::table($table)->insert($rows);
        }

        if ($resync) {
            $this->resyncSequence($table);
        }
    }

    /**
     * MySQL auto-advances AUTO_INCREMENT when an explicit id is inserted;
     * Postgres serial/identity sequences do not. Without this, the next
     * Eloquent-generated id on Postgres would collide with a seeded row.
     */
    private function resyncSequence(string $table): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $maxId = DB::table($table)->max('id');

        if ($maxId) {
            DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), {$maxId})");
        }
    }
}
