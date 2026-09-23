<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PledgeCampaign;
use Illuminate\Http\Request;

class PledgeCampaignController extends Controller
{
    /**
     * Active campaigns available to the logged-in member - JSON mirror of
     * MyPledgesController::browse().
     */
    public function index(Request $request)
    {
        $member = $request->user()->member;

        abort_unless($member, 403, 'No member record found for this user.');

        $campaigns = PledgeCampaign::visibleToMember($member)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'description' => $campaign->description,
                'banner_url' => $campaign->banner_path ? asset('storage/' . $campaign->banner_path) : null,
                'target_amount' => (float) $campaign->target_amount,
                'currency' => $campaign->currency,
                'start_date' => optional($campaign->start_date)->toDateString(),
                'end_date' => optional($campaign->end_date)->toDateString(),
                'allow_anonymous' => $campaign->allow_anonymous,
                'total_pledged' => $campaign->totalPledged(),
                'progress_percent' => $campaign->progressPercent(),
            ]);

        return response()->json($campaigns->values());
    }
}
