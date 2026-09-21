<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Gates Programs & Attendance module actions using the same permission
 * scaffold that AuthorizesPledges uses: User::hasPermission($code), backed
 * by permissions/role_permissions. Per explicit instruction, the
 * PROGRAMS_, ATTENDANCE_, NEW_SOULS_ and PROGRAM_REPORTS_ permission checks
 * are currently relaxed to open - any authenticated user passes - while the
 * codes and call sites stay in place so per-designation enforcement can be
 * switched back on later just by restoring the hasPermission() check below.
 */
trait AuthorizesPrograms
{
    protected function authorizeProgram(string $code)
    {
        abort_unless(Auth::check(), 403, 'You do not have permission to perform this action.');

        // Temporarily open to any authenticated user - re-enable per-code
        // enforcement by restoring:
        // abort_unless($user->role === 'ADMIN' || $user->hasPermission($code), 403, '...');
    }
}
