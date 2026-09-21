<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Gates Pledges-module actions using the app's existing permission scaffold:
 * User::hasPermission($code), backed by the permissions / role_permissions
 * tables. Per explicit instruction, the PLEDGES_* permission check is
 * currently relaxed to open - any authenticated user passes - while the
 * codes and call sites stay in place so per-designation enforcement can be
 * switched back on later just by restoring the hasPermission() check below.
 */
trait AuthorizesPledges
{
    protected function authorizePledge(string $code)
    {
        abort_unless(Auth::check(), 403, 'You do not have permission to perform this action.');

        // Temporarily open to any authenticated user - re-enable per-code
        // enforcement by restoring:
        // abort_unless($user->role === 'ADMIN' || $user->hasPermission($code), 403, '...');
    }
}
