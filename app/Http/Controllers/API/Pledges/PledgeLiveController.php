<?php

namespace App\Http\Controllers\API\Pledges;

use App\Http\Controllers\Concerns\AuthorizesPledges;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\PledgeCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PledgeLiveController extends Controller
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

    public function select()
    {
        $this->authorizePledge('PLEDGES_LIVE_PRESENTATION');

        $churchIds = $this->scopedChurchIds();

        $query = PledgeCampaign::where('live_enabled', true)->whereIn('status', ['active', 'completed']);

        if (!is_null($churchIds)) {
            $query->where(function ($q) use ($churchIds) {
                $q->where('scope', 'global')->orWhereIn('church_id', $churchIds);
            });
        }

        $campaigns = $query->orderByDesc('id')->get();

        return view('portal.pledges.live.select', compact('campaigns'));
    }

    /**
     * Update only the presentation display toggles - decoupled from the full
     * campaign edit form/validation so this stays a quick, low-risk action.
     */
    public function updateSettings(Request $request, PledgeCampaign $campaign)
    {
        $this->authorizePledge('PLEDGES_MANAGE_SETTINGS');

        $flags = ['live_show_amount', 'live_show_pledgers', 'live_show_latest', 'live_show_graph', 'live_show_target', 'live_mask_names'];

        $campaign->update(collect($flags)->mapWithKeys(fn($flag) => [$flag => $request->boolean($flag)])->toArray());

        return back()->with('success', 'Presentation controls updated for ' . $campaign->name . '.');
    }

    public function present(PledgeCampaign $campaign)
    {
        $this->authorizePledge('PLEDGES_LIVE_PRESENTATION');

        abort_unless($campaign->live_enabled, 404, 'Live presentation is not enabled for this campaign.');

        return view('portal.pledges.live.present', compact('campaign'));
    }

    /**
     * JSON payload the presentation screen polls every few seconds.
     */
    public function data(PledgeCampaign $campaign)
    {
        $this->authorizePledge('PLEDGES_LIVE_PRESENTATION');

        $pledged = $campaign->totalPledged();
        $target = (float) $campaign->target_amount;

        $latest = $campaign->live_show_latest
            ? $campaign->pledges()
                ->where('status', '!=', 'cancelled')
                ->with('member.church')
                ->orderByDesc('created_at')
                ->limit(8)
                ->get()
                ->map(fn($p) => [
                    'name' => $campaign->live_mask_names ? $p->displayName() : (optional($p->member)->first_name . ' ' . optional($p->member)->last_name),
                    'amount' => (float) $p->amount,
                    'at' => $p->created_at->toIso8601String(),
                ])
            : [];

        return response()->json([
            'campaign' => $campaign->name,
            'currency' => $campaign->currency,
            'target' => $target,
            'pledged' => $pledged,
            'percent' => $campaign->progressPercent(),
            'pledgers' => $campaign->pledgersCount(),
            'latest' => $latest,
            'show' => [
                'amount' => (bool) $campaign->live_show_amount,
                'pledgers' => (bool) $campaign->live_show_pledgers,
                'latest' => (bool) $campaign->live_show_latest,
                'graph' => (bool) $campaign->live_show_graph,
                'target' => (bool) $campaign->live_show_target,
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
