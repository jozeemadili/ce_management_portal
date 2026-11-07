<?php

namespace App\Console\Commands;

use App\Models\ProgramAuditLog;
use App\Models\User;
use App\Services\AccountLogin;
use App\Services\MenuAccess;
use Illuminate\Console\Command;

/**
 * Makes a user the super admin (role ADMIN): they see every menu on the
 * portal and in the app, and give menus to everyone else under
 * Security & Settings > Access Control.
 *
 *   php artisan access:super-admin 0712345678
 *   php artisan access:super-admin name@example.com
 *   php artisan access:super-admin 0712345678 --remove
 *   php artisan access:super-admin --list
 */
class MakeSuperAdmin extends Command
{
    protected $signature = 'access:super-admin {login? : Email or phone number of the user} {--remove : Take super admin away} {--list : Show who is super admin}';

    protected $description = 'Make a user super admin (sees everything and assigns menus to others)';

    public function handle(MenuAccess $access): int
    {
        if ($this->option('list') || !$this->argument('login')) {
            $admins = User::where('role', 'ADMIN')->orderBy('first_name')->get();
            $this->info($admins->count() . ' super admin(s):');
            foreach ($admins as $a) {
                $this->line('  - ' . trim($a->first_name . ' ' . $a->last_name) . ' · ' . ($a->email ?: '-') . ' · ' . ($a->mobile ? '0' . $a->mobile : '-'));
            }

            return self::SUCCESS;
        }

        $user = AccountLogin::findUser($this->argument('login'));
        if (!$user) {
            $this->error('No user found with that email or phone number.');

            return self::FAILURE;
        }

        $name = trim($user->first_name . ' ' . $user->last_name);
        $old = ['role' => $user->role];

        if ($this->option('remove')) {
            if ($user->role !== 'ADMIN') {
                $this->info("{$name} is not a super admin.");

                return self::SUCCESS;
            }
            if (User::where('role', 'ADMIN')->count() <= 1) {
                $this->error("{$name} is the only super admin. Make someone else super admin first.");

                return self::FAILURE;
            }
            $user->update(['role' => 'Customer Admin']);
            $this->info("{$name} is no longer super admin. They now see only the menus given to them.");
        } else {
            if ($user->role === 'ADMIN') {
                $this->info("{$name} is already super admin.");

                return self::SUCCESS;
            }
            $user->update(['role' => 'ADMIN']);
            $this->info("{$name} is now super admin: sees every menu and can give menus to others under Security & Settings > Access Control.");
        }

        $access->forget($user);
        ProgramAuditLog::record('access.super_admin_' . ($this->option('remove') ? 'removed' : 'given'), $user, $old, ['role' => $user->role, 'by' => 'artisan']);

        return self::SUCCESS;
    }
}
