<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Church;
use Illuminate\Support\Collection;

trait ScopesChurches
{
    /**
     * Active churches the user works with - one rule everywhere
     * (App\Services\ChurchScope): super admin = all; the pastor / head of a
     * church = that church and every church under it; anyone else = their
     * own church only.
     */
    protected function visibleChurches(): Collection
    {
        return Church::where('status', 'ACTIVE')
            ->whereIn('id', app(\App\Services\ChurchScope::class)->ids())
            ->orderBy('name')
            ->get();
    }
}
