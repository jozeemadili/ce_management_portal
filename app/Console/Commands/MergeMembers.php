<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Services\MemberMerger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Merges duplicate records into one (same as Church Setup > Duplicate
 * Members, for many groups at once):
 *
 *   php artisan members:merge 161:277 138:246,17 --dry-run
 *
 * Each argument is KEEP:DUPLICATE[,DUPLICATE...]. --dry-run does every
 * merge inside a transaction and rolls it all back, showing what would move.
 */
class MergeMembers extends Command
{
    protected $signature = 'members:merge {groups* : KEEP:DUP[,DUP...] e.g. 161:277} {--dry-run : Show what would happen, change nothing} {--force : Do not ask for each group}';

    protected $description = 'Merge duplicate member records into the one kept (see members:duplicates)';

    public function handle(MemberMerger $merger)
    {
        $plan = [];
        foreach ($this->argument('groups') as $arg) {
            if (!preg_match('/^(\d+):(\d+(?:,\d+)*)$/', trim($arg), $m)) {
                $this->error("Bad group \"{$arg}\" - use KEEP:DUP[,DUP...], e.g. 161:277");

                return self::FAILURE;
            }
            $plan[] = [(int) $m[1], array_map('intval', explode(',', $m[2]))];
        }

        $dry = (bool) $this->option('dry-run');
        if ($dry) {
            $this->warn('DRY RUN - nothing will be changed.');
            DB::beginTransaction();
        }

        $done = 0;
        try {
            foreach ($plan as [$keepId, $dupIds]) {
                $keep = Member::with('church')->find($keepId);
                $dups = Member::with('church')->whereIn('id', $dupIds)->get();
                $this->line('');

                if (!$keep || $dups->count() !== count($dupIds) || in_array($keepId, $dupIds, true)) {
                    $this->error("Skipped {$keepId}:" . implode(',', $dupIds) . ' - a record is missing (already merged?) or listed twice.');
                    continue;
                }

                $name = fn ($m) => "#{$m->id} " . trim($m->first_name . ' ' . $m->last_name) . ' (' . optional($m->church)->name . ', ' . ($m->phone ?: 'no phone') . ')';
                $this->info('Keep  ' . $name($keep));
                foreach ($dups as $d) {
                    $same = mb_strtolower(trim($d->first_name . ' ' . $d->last_name)) === mb_strtolower(trim($keep->first_name . ' ' . $keep->last_name));
                    $this->line('Merge ' . $name($d) . ($same ? '' : '   <comment>! different name</comment>'));
                }

                if (!$dry && !$this->option('force') && !$this->confirm('Merge these?', false)) {
                    $this->line('Skipped.');
                    continue;
                }

                $totals = [];
                foreach ($dups as $d) {
                    foreach ($merger->merge($keep, $d, null) as $k => $n) {
                        $totals[$k] = ($totals[$k] ?? 0) + $n;
                    }
                }
                $moved = collect($totals)->filter()->map(fn ($n, $k) => "{$n} " . str_replace('_', ' ', $k))->implode(', ');
                $this->line(($dry ? 'Would move: ' : 'Moved: ') . ($moved ?: 'nothing besides the record'));
                $done++;
            }
        } finally {
            if ($dry) {
                DB::rollBack();
            }
        }

        $this->line('');
        $this->info($dry ? "Dry run: {$done} group(s) checked, nothing changed." : "Merged {$done} group(s).");

        return self::SUCCESS;
    }
}
