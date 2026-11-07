<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserMenuGrant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Which menus a user can see and which pages they can open. The sidebar
 * (@menuVisible) and the route guard (CheckMenuAccess) both ask this class,
 * so a hidden link and a blocked page never disagree.
 *
 * - ADMIN users see everything.
 * - Everyone gets config('menu_access.always') (Summary, Your Profile).
 * - A main menu given to a user brings all its sub-menus; a sub-menu on its
 *   own also brings its main menu header.
 */
class MenuAccess
{
    private const CACHE_SECONDS = 300;

    /** @return array<string, array{label: string, children: array<string, string>}> */
    public function catalog(): array
    {
        return config('menu_access.menus', []);
    }

    /** @return array<int, string> every main and sub-menu key */
    public function allKeys(): array
    {
        $keys = [];
        foreach ($this->catalog() as $main => $menu) {
            $keys[] = $main;
            array_push($keys, ...array_keys($menu['children']));
        }

        return $keys;
    }

    public function isAdmin(?User $user): bool
    {
        return $user && $user->role === 'ADMIN';
    }

    /** @return array<int, string> menu keys this user can see (main and sub-menus, expanded) */
    public function keys(?User $user): array
    {
        if (!$user) {
            return [];
        }
        if ($this->isAdmin($user)) {
            return $this->allKeys();
        }

        return Cache::remember("menu-access:{$user->id}", self::CACHE_SECONDS, fn () => $this->expand(array_merge(
            config('menu_access.always', []),
            UserMenuGrant::where('user_id', $user->id)->pluck('menu_key')->all()
        )));
    }

    /** @param  string|array<int, string>  $keys  true if the user has ANY of them */
    public function can(?User $user, string|array $keys): bool
    {
        return (bool) array_intersect((array) $keys, $this->keys($user));
    }

    /** @return array<int, string> the raw grants (what an admin ticked) */
    public function granted(User $user): array
    {
        return UserMenuGrant::where('user_id', $user->id)->pluck('menu_key')->all();
    }

    /**
     * Replaces a user's grants. A ticked main menu is stored on its own (it
     * covers all its sub-menus); unknown keys are ignored.
     *
     * @param  array<int, string>  $keys
     * @return array{0: array<int, string>, 1: array<int, string>} [before, after]
     */
    public function setGrants(User $user, array $keys, ?int $grantedBy): array
    {
        $valid = $this->allKeys();
        $keys = array_values(array_unique(array_intersect($keys, $valid)));

        // Drop sub-menus already covered by a ticked main menu.
        $mains = array_keys($this->catalog());
        $keys = array_values(array_filter($keys, function ($key) use ($keys, $mains) {
            $parent = $this->parentOf($key);

            return in_array($key, $mains, true) || !in_array($parent, $keys, true);
        }));

        $before = $this->granted($user);
        UserMenuGrant::where('user_id', $user->id)->whereNotIn('menu_key', $keys ?: [''])->delete();
        foreach (array_diff($keys, $before) as $key) {
            UserMenuGrant::create(['user_id' => $user->id, 'menu_key' => $key, 'granted_by' => $grantedBy]);
        }
        $this->forget($user);

        sort($before);
        sort($keys);

        return [$before, $keys];
    }

    public function forget(User $user): void
    {
        Cache::forget("menu-access:{$user->id}");
    }

    /**
     * The menu key(s) that open a route, from config('menu_access.routes')
     * (first matching pattern wins), or null when the route only needs a login.
     *
     * @return array<int, string>|null
     */
    public function keysForRoute(?string $routeName): ?array
    {
        if (!$routeName) {
            return null;
        }
        foreach (config('menu_access.routes', []) as $pattern => $keys) {
            if (Str::is($pattern, $routeName)) {
                return (array) $keys;
            }
        }

        return null;
    }

    public function parentOf(string $key): ?string
    {
        foreach ($this->catalog() as $main => $menu) {
            if (array_key_exists($key, $menu['children'])) {
                return $main;
            }
        }

        return null;
    }

    private function expand(array $raw): array
    {
        $catalog = $this->catalog();
        $out = [];
        foreach (array_unique($raw) as $key) {
            if (isset($catalog[$key])) {
                $out[] = $key;
                array_push($out, ...array_keys($catalog[$key]['children']));
            } elseif ($parent = $this->parentOf($key)) {
                $out[] = $key;
                $out[] = $parent;
            }
        }

        return array_values(array_unique($out));
    }
}
