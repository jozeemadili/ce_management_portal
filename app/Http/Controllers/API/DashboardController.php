<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pledge;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Main landing dashboard: a countdown carousel of upcoming special
     * programs (banner-driven, same visibility rule as the Programs
     * registration desk) and the logged-in member's own pledge summary.
     */
    public function index()
    {
        $member = Auth::user()->member;

        $upcomingQuery = Program::where('classification', 'special')
            ->where('status', 'active')
            ->whereDate('start_date', '>=', now()->toDateString())
            ->orderBy('start_date');

        if ($member) {
            $upcomingQuery->visibleToMember($member);
        }

        $upcomingPrograms = $upcomingQuery->limit(8)->get();

        $pledgeSummary = null;

        if ($member) {
            $pledges = Pledge::with('campaign')
                ->where('member_id', $member->id)
                ->where('status', '!=', 'cancelled')
                ->orderByDesc('created_at')
                ->get();

            $totalPledged = (float) $pledges->sum('amount');
            $totalFulfilled = (float) $pledges->sum(fn($p) => $p->totalFulfilled());

            $pledgeSummary = [
                'count' => $pledges->count(),
                'total_pledged' => $totalPledged,
                'total_fulfilled' => $totalFulfilled,
                'outstanding' => max(0, $totalPledged - $totalFulfilled),
                'recent' => $pledges->take(5),
            ];
        }

        return view('admin.dashboard.home', compact('upcomingPrograms', 'pledgeSummary'));
    }
}
