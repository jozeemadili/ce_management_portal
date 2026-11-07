<?php

namespace App\Http\Middleware;

use App\Services\MenuAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Blocks a portal page unless the user was given one of the menus that
 * open it (config/menu_access.php 'routes') - the same check the sidebar
 * uses, so a hidden menu can't be opened by typing its URL.
 */
class CheckMenuAccess
{
    public function __construct(private MenuAccess $access)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $keys = $this->access->keysForRoute(optional($request->route())->getName());

        if ($keys !== null && !$this->access->can($request->user(), $keys)) {
            abort(403, 'You do not have access to this page. Ask the administrator to give you this menu under Security & Settings > Access Control.');
        }

        return $next($request);
    }
}
