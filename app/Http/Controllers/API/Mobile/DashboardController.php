<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Pledge;
use App\Models\Program;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * JSON mirror of API\DashboardController::index() (the web /v1/dashboard
     * page): upcoming special programs the member can see, plus their own
     * pledge summary.
     */
    public function index(Request $request)
    {
        $member = $request->user()->member;

        $upcomingQuery = Program::where('classification', 'special')
            ->where('status', 'active')
            ->whereDate('start_date', '>=', now()->toDateString())
            ->orderBy('start_date');

        if ($member) {
            $upcomingQuery->visibleToMember($member);
        }

        $upcomingPrograms = $upcomingQuery->limit(8)->get()->map(fn ($program) => [
            'id' => $program->id,
            'name' => $program->name,
            'description' => $program->description,
            'banner_url' => $program->banner_path ? asset('storage/' . $program->banner_path) : null,
            'location' => $program->location,
            'start_date' => optional($program->start_date)->toDateString(),
            'start_time' => $program->start_time,
        ]);

        $pledgeSummary = null;

        if ($member) {
            $pledges = Pledge::with('campaign')
                ->where('member_id', $member->id)
                ->where('status', '!=', 'cancelled')
                ->orderByDesc('created_at')
                ->get();

            $totalPledged = (float) $pledges->sum('amount');
            $totalFulfilled = (float) $pledges->sum(fn ($p) => $p->totalFulfilled());

            $pledgeSummary = [
                'count' => $pledges->count(),
                'total_pledged' => $totalPledged,
                'total_fulfilled' => $totalFulfilled,
                'outstanding' => max(0, $totalPledged - $totalFulfilled),
                'recent' => $pledges->take(5)->map(fn ($p) => [
                    'id' => $p->id,
                    'reference' => $p->pledge_reference,
                    'campaign' => optional($p->campaign)->name,
                    'currency' => optional($p->campaign)->currency,
                    'amount' => (float) $p->amount,
                    'status' => $p->status,
                ])->values(),
            ];
        }

        return response()->json([
            'upcoming_programs' => $upcomingPrograms,
            'pledge_summary' => $pledgeSummary,
        ]);
    }
}
