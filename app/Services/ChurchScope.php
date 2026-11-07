<?php

namespace App\Services;

use App\Models\Church;
use App\Models\HeadOfChurchUnit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Which churches a user works with - one rule for the whole portal and app:
 *
 *  - super admin (role ADMIN): every church;
 *  - the current head (pastor) of a church: that church and every church
 *    under it, at any depth (e.g. the head of a zone sees the whole zone);
 *  - anyone else: only their own church.
 *
 * "Current head" is the latest head_of_church_units row of a church, so a
 * pastor who was replaced no longer gets the churches.
 */
class ChurchScope
{
    private const CACHE_SECONDS = 300;

    /** @var array<int, array<int, int>> per-request memo */
    private array $memo = [];

    /** @return array<int, int> church ids (any status) */
    public function ids(?User $user = null): array
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return [];
        }

        return $this->memo[$user->id] ??= Cache::remember(
            "church-scope:{$user->id}",
            self::CACHE_SECONDS,
            fn () => $this->resolve($user)
        );
    }

    /** Churches this user heads now (as pastor / head of unit). @return array<int, int> */
    public function headedChurchIds(?User $user = null): array
    {
        $user = $user ?? Auth::user();
        $memberId = optional(optional($user)->member)->id;
        if (!$memberId) {
            return [];
        }

        // Latest head per church, then keep the churches where that is this member.
        $latest = HeadOfChurchUnit::query()
            ->whereIn('id', HeadOfChurchUnit::selectRaw('MAX(id)')->groupBy('unit_id'))
            ->get(['unit_id', 'head_of_unit']);

        return $latest->where('head_of_unit', $memberId)->pluck('unit_id')->map(fn ($id) => (int) $id)->values()->all();
    }

    public function isHead(?User $user = null): bool
    {
        return (bool) $this->headedChurchIds($user);
    }

    public function sees(int $churchId, ?User $user = null): bool
    {
        return in_array($churchId, $this->ids($user), true);
    }

    /** Stops a change to a church (or its members, cells...) outside the user's churches. */
    public function authorize(?int $churchId, string $message = 'This church is not one of yours.'): void
    {
        $user = Auth::user();
        if ($user && $user->role === 'ADMIN') {
            return;
        }
        abort_unless($churchId && $this->sees($churchId, $user), 403, $message);
    }

    public function forget(User $user): void
    {
        unset($this->memo[$user->id]);
        Cache::forget("church-scope:{$user->id}");
    }

    /**
     * After churches or their heads change: everyone's churches (and the
     * pastor menus that come with heading a church) may change.
     */
    public function forgetAll(): void
    {
        $this->memo = [];
        User::pluck('id')->each(function ($id) {
            Cache::forget("church-scope:{$id}");
            Cache::forget("menu-access:{$id}");
        });
    }

    private function resolve(User $user): array
    {
        if ($user->role === 'ADMIN') {
            return Church::pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        $own = optional($user->member)->church_id;
        $ids = $own ? [(int) $own] : [];

        $headed = $this->headedChurchIds($user);
        if ($headed) {
            $byParent = Church::get(['id', 'parent_church_id'])->groupBy('parent_church_id');
            $queue = $headed;
            for ($i = 0; $i < count($queue); $i++) {
                foreach ($byParent->get($queue[$i], collect()) as $child) {
                    if (!in_array((int) $child->id, $queue, true)) {
                        $queue[] = (int) $child->id;
                    }
                }
            }
            $ids = array_merge($ids, $queue);
        }

        return array_values(array_unique($ids));
    }
}
