<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Accounts still on the shared initial password must choose their own
 * before using anything else: web requests go to the set-password page,
 * API requests get a 403 the app recognises (must_change_password).
 */
class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->must_change_password && !$request->routeIs('logout')) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Please set a new password before continuing.',
                    'must_change_password' => true,
                ], 403);
            }

            return redirect()->route('password.first-change');
        }

        return $next($request);
    }
}
