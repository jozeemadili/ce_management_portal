<?php

namespace App\Console\Commands;

use App\Services\MemberMerger;
use Illuminate\Console\Command;

/**
 * Read-only: lists people who look registered more than once (same name,
 * phone in any format, or email). Merge them on Church Setup > Duplicate
 * Members. Changes nothing.
 */
class ListDuplicateMembers extends Command
{
    protected $signature = 'members:duplicates {--limit=50 : Groups to show}';

    protected $description = 'List people who look registered more than once (read-only)';

    public function handle(MemberMerger $merger)
    {
        $groups = $merger->duplicateGroups();

        if ($groups->isEmpty()) {
            $this->info('No likely duplicates found.');

            return self::SUCCESS;
        }

        $this->info($groups->count() . ' group(s) of likely duplicates, ' . $groups->sum(fn ($g) => $g['members']->count()) . ' records.');

        foreach ($groups->take((int) $this->option('limit')) as $i => $group) {
            $this->line('');
            $this->line('#' . ($i + 1) . '  same ' . implode(' + ', $group['reasons']));
            $this->table(['ID', 'Name', 'Type', 'Church', 'Phones', 'Email', 'Login', 'Attended', 'Reg.', 'Pledges', 'Added'], $group['members']->map(fn ($m) => [
                $m->id,
                trim($m->first_name . ' ' . $m->last_name),
                $m->member_type,
                optional($m->church)->name,
                implode(', ', $m->allPhones()),
                $m->email,
                $m->user_id ? 'yes' : '',
                $m->activity['attended'],
                $m->activity['registrations'],
                $m->activity['pledges'],
                optional($m->created_at)->format('Y-m-d'),
            ])->all());
        }

        $this->line('');
        $this->line('Nothing was changed. Merge them on Church Setup > Duplicate Members.');

        return self::SUCCESS;
    }
}
