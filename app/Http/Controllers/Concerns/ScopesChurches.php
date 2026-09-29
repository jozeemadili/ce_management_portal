<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Church;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

trait ScopesChurches
{
    /**
     * Churches the user may work with: their own church and every church
     * under it; the top-level church (or a user without a member record)
     * sees all active churches.
     */
    protected function visibleChurches(): Collection
    {
        $all = Church::where('status', 'ACTIVE')->orderBy('name')->get();
        $member = Auth::user()->member;
        $own = $member ? $all->firstWhere('id', $member->church_id) : null;

        if (!$own || is_null($own->parent_church_id)) {
            return $all;
        }

        $byParent = $all->groupBy('parent_church_id');
        $ids = [$own->id];
        for ($i = 0; $i < count($ids); $i++) {
            foreach ($byParent->get($ids[$i], collect()) as $child) {
                $ids[] = $child->id;
            }
        }

        return $all->whereIn('id', $ids)->values();
    }
}
