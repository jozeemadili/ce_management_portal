<?php

namespace App\Http\Controllers\API\Pledges;

use App\Http\Controllers\Concerns\AuthorizesPledges;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Pledge;
use App\Models\PledgeCampaign;
use App\Models\PledgeContribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PledgeDashboardController extends Controller
{
    use AuthorizesPledges;

    private function scopedChurchIds()
    {
        $member = Auth::user()->member;

        if (!$member) {
            if (Auth::user()->role === 'ADMIN') {
                return null;
            }
            abort(403, 'No member record found for this user.');
        }

        $userChurch = Church::find($member->church_id);

        if ($userChurch && is_null($userChurch->parent_church_id)) {
            return null;
        }

        return Church::where('id', $userChurch->id ?? 0)
            ->orWhere('parent_church_id', $userChurch->id ?? 0)
            ->pluck('id');
    }

    public function index(Request $request)
    {
        $this->authorizePledge('PLEDGES_VIEW');

        $churchIds = $this->scopedChurchIds();

        $campaignsQuery = PledgeCampaign::query();
        if (!is_null($churchIds)) {
            $campaignsQuery->where(function ($q) use ($churchIds) {
                $q->where('scope', 'global')->orWhereIn('church_id', $churchIds);
            });
        }
        $campaigns = $campaignsQuery->get();
        $campaignIds = $campaigns->pluck('id');

        $pledgesQuery = Pledge::whereIn('campaign_id', $campaignIds)->where('status', '!=', 'cancelled');
        $allPledges = $pledgesQuery->get();

        $totalPledged = (float) $allPledges->sum('amount');
        $totalFulfilled = (float) PledgeContribution::whereIn('pledge_id', $allPledges->pluck('id'))->sum('amount');

        $stats = [
            'active_campaigns' => $campaigns->where('status', 'active')->count(),
            'total_pledged' => $totalPledged,
            'total_fulfilled' => $totalFulfilled,
            'total_outstanding' => max(0, $totalPledged - $totalFulfilled),
            'total_pledgers' => $allPledges->pluck('member_id')->unique()->count(),
            'today_pledges' => $allPledges->filter(fn($p) => $p->created_at && $p->created_at->isToday())->count(),
            'today_amount' => $allPledges->filter(fn($p) => $p->created_at && $p->created_at->isToday())->sum('amount'),
        ];

        // Pledge growth (last 30 days, daily)
        $since = now()->subDays(29)->startOfDay();
        $growth = $allPledges->filter(fn($p) => $p->created_at && $p->created_at->gte($since))
            ->groupBy(fn($p) => $p->created_at->format('Y-m-d'))
            ->map->sum('amount')
            ->sortKeys();

        // Pledgers over time (same window, count of distinct pledgers per day)
        $pledgersOverTime = $allPledges->filter(fn($p) => $p->created_at && $p->created_at->gte($since))
            ->groupBy(fn($p) => $p->created_at->format('Y-m-d'))
            ->map(fn($group) => $group->pluck('member_id')->unique()->count())
            ->sortKeys();

        // Campaign performance (top 8 by target)
        $campaignPerformance = $campaigns->sortByDesc('target_amount')->take(8)->map(fn($c) => [
            'name' => $c->name,
            'percent' => $c->progressPercent(),
        ])->values();

        $recentPledges = Pledge::with(['campaign', 'member.church'])
            ->whereIn('campaign_id', $campaignIds)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('portal.pledges.dashboard', compact(
            'stats', 'growth', 'pledgersOverTime', 'campaignPerformance', 'recentPledges',
            'totalPledged', 'totalFulfilled'
        ));
    }
}
