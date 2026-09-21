<?php

namespace App\Http\Controllers\API\Pledges;

use App\Exports\MemberPledgesExport;
use App\Http\Controllers\Concerns\AuthorizesPledges;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Member;
use App\Models\Pledge;
use App\Models\PledgeAuditLog;
use App\Models\PledgeCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PledgeManagementController extends Controller
{
    use AuthorizesPledges;

    /**
     * Member IDs visible to the current user: their church + sub-churches,
     * or unrestricted for root / a pure ADMIN account with no member record.
     */
    private function scopedMemberChurchIds()
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

    private function scopedPledgesQuery(Request $request)
    {
        $churchIds = $this->scopedMemberChurchIds();

        $query = Pledge::with(['campaign', 'member.church', 'recorder']);

        if (!is_null($churchIds)) {
            $query->whereHas('member', fn($q) => $q->whereIn('church_id', $churchIds));
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($outer) use ($search) {
                $outer->where('pledge_reference', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        return $query->orderByDesc('created_at');
    }

    public function index(Request $request)
    {
        $this->authorizePledge('PLEDGES_VIEW');

        $pledges = $this->scopedPledgesQuery($request)->paginate(15)->withQueryString();

        $churchIds = $this->scopedMemberChurchIds();
        $campaigns = is_null($churchIds)
            ? PledgeCampaign::orderBy('name')->get()
            : PledgeCampaign::where('scope', 'global')->orWhereIn('church_id', $churchIds)->orderBy('name')->get();

        $stats = [
            'total' => (clone $this->scopedPledgesQuery($request))->count(),
            'staff' => (clone $this->scopedPledgesQuery($request))->where('source', 'staff')->count(),
            'pledged' => (clone $this->scopedPledgesQuery($request))->where('status', '!=', 'cancelled')->sum('amount'),
        ];

        $churches = is_null($churchIds)
            ? Church::orderBy('name')->get()
            : Church::whereIn('id', $churchIds)->orderBy('name')->get();

        return view('portal.pledges.manage.index', compact('pledges', 'campaigns', 'stats', 'churches'));
    }

    /**
     * Shapes a member (with their existing, non-cancelled pledges) into the
     * JSON payload the "Record Pledge on Behalf" modal expects - shared by
     * search results and newly-created new souls so the frontend can treat
     * both the same way once a person is selected.
     */
    private function memberPayload(Member $member): array
    {
        return [
            'id' => $member->id,
            'name' => trim($member->first_name . ' ' . $member->last_name),
            'phone' => $member->phone,
            'church' => optional($member->church)->name,
            'is_new_soul' => $member->member_type === 'new_soul',
            'pledges' => $member->pledges()
                ->with('campaign')
                ->where('status', '!=', 'cancelled')
                ->get()
                ->map(fn($p) => [
                    'campaign_id' => $p->campaign_id,
                    'campaign_name' => optional($p->campaign)->name,
                    'currency' => optional($p->campaign)->currency,
                    'amount' => (float) $p->amount,
                ]),
        ];
    }

    /**
     * AJAX member lookup for the "Record Pledge on Behalf" search box -
     * name, phone, or email, scoped the same way as the pledge listing.
     * Each result carries the member's existing pledges so the frontend can
     * flag "already pledged to this campaign" once a campaign is chosen.
     */
    public function searchMembers(Request $request)
    {
        $this->authorizePledge('PLEDGES_RECORD_ON_BEHALF');

        $churchIds = $this->scopedMemberChurchIds();
        $search = trim((string) $request->get('q'));

        $query = Member::with('church')->where('member_type', 'member')->limit(15);

        if (!is_null($churchIds)) {
            $query->whereIn('church_id', $churchIds);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $results = $query->get()->map(fn($m) => $this->memberPayload($m));

        return response()->json($results);
    }

    /**
     * Creates a brand-new person on the spot when the "Record Pledge on
     * Behalf" search comes back empty - same "new soul" concept used on the
     * Programs registration desk: a tagged member_type=new_soul row, found
     * again by phone next time instead of duplicated.
     */
    public function createMember(Request $request)
    {
        $this->authorizePledge('PLEDGES_RECORD_ON_BEHALF');

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'church_id' => 'required|exists:churches,id',
        ]);

        $member = Member::findOrCreateNewSoul([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'phone' => $data['phone'],
        ], (int) $data['church_id'], Auth::id());

        $member->load('church');

        return response()->json($this->memberPayload($member));
    }

    public function recordOnBehalf(Request $request)
    {
        $this->authorizePledge('PLEDGES_RECORD_ON_BEHALF');

        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'campaign_id' => 'required|exists:pledge_campaigns,id',
            'amount' => 'required|numeric|min:1',
            'frequency' => 'required|in:one_time,weekly,monthly,custom',
            'notes' => 'nullable|string',
        ]);

        $member = Member::findOrFail($data['member_id']);
        $campaign = PledgeCampaign::findOrFail($data['campaign_id']);

        abort_unless($campaign->status === 'active', 422, 'This campaign is not currently accepting pledges.');
        abort_unless(
            $campaign->scope === 'global' || $campaign->church_id === $member->church_id,
            422,
            'This campaign is not available to the selected member\'s church.'
        );

        $pledge = Pledge::createFor($member, $campaign, [
            'amount' => $data['amount'],
            'frequency' => $data['frequency'],
            'notes' => $data['notes'] ?? null,
            'anonymous_display' => false,
        ], 'staff', Auth::id());

        PledgeAuditLog::record('pledge.recorded_by_staff', $pledge, null, $pledge->toArray());

        return back()->with('success', "Pledge {$pledge->pledge_reference} recorded on behalf of {$member->first_name} {$member->last_name}.");
    }

    public function export(Request $request)
    {
        $this->authorizePledge('PLEDGES_VIEW_REPORTS');

        $pledges = $this->scopedPledgesQuery($request)->get();

        return Excel::download(new MemberPledgesExport($pledges), 'member-pledges-' . now()->format('Y-m-d_His') . '.xlsx');
    }
}
