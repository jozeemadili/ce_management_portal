<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\User;
use App\Services\AccountLogin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * One-off repair: church members (not first-time visitors) that have no
 * login account - e.g. added by the Excel upload before it created
 * accounts - get one: phone (or email) + the initial password, to be changed
 * on first login. Members whose phone/email already belongs to another
 * account are listed and left alone.
 */
class CreateMemberLogins extends Command
{
    protected $signature = 'members:create-logins {--dry-run : List what would be created without changing anything}';

    protected $description = 'Create login accounts for church members that do not have one';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $members = Member::where('member_type', 'member')->whereNull('user_id')->orderBy('id')->get();

        if ($members->isEmpty()) {
            $this->info('Every church member already has a login account.');

            return self::SUCCESS;
        }

        $passwordHash = Hash::make(AccountLogin::defaultPassword());
        $created = 0;
        $skipped = [];
        $seenMobiles = [];
        $seenEmails = [];

        foreach ($members as $member) {
            $name = trim($member->first_name . ' ' . $member->last_name);
            $mobile = AccountLogin::normaliseMobile($member->phone);
            $email = $member->email ? mb_strtolower(trim($member->email)) : null;

            $reason = null;
            if (!$member->first_name || !$member->last_name) {
                $reason = 'first or last name missing';
            } elseif (!$mobile) {
                $reason = 'no valid phone number';
            } elseif (isset($seenMobiles[$mobile]) || User::where('mobile', $mobile)->exists()) {
                $reason = 'phone already used by another login';
            } elseif ($email && (isset($seenEmails[$email]) || User::whereRaw('LOWER(email) = ?', [$email])->exists())) {
                $reason = 'email already used by another login';
            }

            if ($reason) {
                $skipped[] = [$member->id, $name, $member->phone, $reason];
                continue;
            }

            $seenMobiles[$mobile] = true;
            if ($email) {
                $seenEmails[$email] = true;
            }

            if (!$dryRun) {
                DB::transaction(function () use ($member, $mobile, $email, $passwordHash) {
                    $user = AccountLogin::createMemberUser($member->first_name, $member->last_name, $mobile, $email, null, $passwordHash);
                    $member->update(['user_id' => $user->id]);
                });
            }
            $created++;
        }

        $this->info(($dryRun ? 'Would create' : 'Created') . " {$created} login account(s). Initial password: "
            . AccountLogin::defaultPassword() . ' (members must change it on first login).');

        if ($skipped) {
            $this->warn(count($skipped) . ' member(s) skipped - fix them in Member Management:');
            $this->table(['Member ID', 'Name', 'Phone', 'Reason'], $skipped);
        }

        if ($dryRun) {
            $this->line('Dry run - nothing was changed. Run without --dry-run to create the accounts.');
        }

        return self::SUCCESS;
    }
}
