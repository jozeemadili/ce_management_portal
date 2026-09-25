<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Member;
use App\Models\Pledge;
use App\Models\PledgeAuditLog;
use App\Models\PledgeCampaign;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PledgeController extends Controller
{
    private function currentMember(Request $request): Member
    {
        $member = $request->user()->member;

        abort_unless($member, 403, 'No member record found for this user. Pledging requires a linked member profile.');

        return $member;
    }

    /**
     * Church IDs the acting member may search/pledge within: their own
     * church plus its direct sub-churches. Mirrors
     * PledgeManagementController::scopedMemberChurchIds(), minus the
     * unrestricted-ADMIN branch - this is a member-facing endpoint, always
     * bounded to the member's own congregation.
     */
    private function scopedChurchIds(Member $member): array
    {
        return Church::where('id', $member->church_id)
            ->orWhere('parent_church_id', $member->church_id)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Shapes a member for the "pledge for someone else" search results -
     * includes their existing non-cancelled pledges so the client can flag
     * "already pledged to this campaign", same as the web
     * "Record Pledge on Behalf" screen.
     */
    private function memberPayload(Member $member): array
    {
        return [
            'id' => $member->id,
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'name' => trim($member->first_name . ' ' . $member->last_name),
            'phone' => $member->phone,
            'church' => optional($member->church)->name,
            'existing_pledges' => $member->pledges()
                ->with('campaign')
                ->where('status', '!=', 'cancelled')
                ->get()
                ->map(fn ($p) => [
                    'campaign_id' => $p->campaign_id,
                    'campaign_name' => optional($p->campaign)->name,
                    'currency' => optional($p->campaign)->currency,
                    'amount' => (float) $p->amount,
                ])->values(),
        ];
    }

    private function pledgePayload(Pledge $pledge, ?Member $viewer = null, bool $withContributions = false): array
    {
        $payload = [
            'id' => $pledge->id,
            'reference' => $pledge->pledge_reference,
            'campaign_id' => $pledge->campaign_id,
            'campaign' => optional($pledge->campaign)->name,
            'currency' => optional($pledge->campaign)->currency,
            'amount' => (float) $pledge->amount,
            'frequency' => $pledge->frequency,
            'status' => $pledge->status,
            'total_fulfilled' => $pledge->totalFulfilled(),
            'outstanding' => $pledge->outstanding(),
            'notes' => $pledge->notes,
            'pledged_at' => optional($pledge->pledged_at)->toDateTimeString(),
            'made_for_self' => $viewer ? $pledge->member_id === $viewer->id : null,
            'member' => $pledge->relationLoaded('member') && $pledge->member ? [
                'id' => $pledge->member->id,
                'name' => trim($pledge->member->first_name . ' ' . $pledge->member->last_name),
            ] : null,
        ];

        if ($withContributions) {
            $payload['contributions'] = $pledge->contributions->map(fn ($c) => [
                'id' => $c->id,
                'amount' => (float) $c->amount,
                'payment_date' => optional($c->payment_date)->toDateString(),
                'payment_method' => $c->payment_method,
                'payment_reference' => $c->payment_reference,
                'notes' => $c->notes,
            ])->values();
        }

        return $payload;
    }

    /**
     * AJAX member lookup for "pledge on behalf of someone else" - scoped to
     * the acting member's own church + sub-churches, unlike the staff-facing
     * PledgeManagementController::searchMembers() which can span a full
     * hierarchy.
     */
    public function searchMembers(Request $request)
    {
        $member = $this->currentMember($request);
        $search = trim((string) $request->get('q'));

        abort_if($search === '', 422, 'Provide a search term.');

        $churchIds = $this->scopedChurchIds($member);

        $results = Member::with('church')
            ->where('member_type', 'member')
            ->whereIn('church_id', $churchIds)
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(15)
            ->get()
            ->map(fn ($m) => $this->memberPayload($m));

        return response()->json($results->values());
    }

    public function store(Request $request)
    {
        $actingMember = $this->currentMember($request);

        $data = $request->validate([
            'campaign_id' => 'required|exists:pledge_campaigns,id',
            'amount' => 'required|numeric|min:1',
            'frequency' => 'required|in:one_time,weekly,monthly,custom',
            'notes' => 'nullable|string',
            'member_id' => 'nullable|exists:members,id',
        ]);

        $targetMember = $actingMember;

        if (!empty($data['member_id']) && (int) $data['member_id'] !== $actingMember->id) {
            $targetMember = Member::findOrFail($data['member_id']);

            abort_unless(
                in_array($targetMember->church_id, $this->scopedChurchIds($actingMember)),
                403,
                'You may only pledge on behalf of members of your own church.'
            );
        }

        $campaign = PledgeCampaign::findOrFail($data['campaign_id']);

        abort_unless($campaign->status === 'active', 422, 'This campaign is not currently accepting pledges.');
        abort_unless(
            $campaign->scope === 'global' || $campaign->church_id === $targetMember->church_id,
            422,
            "This campaign is not available to this member's church."
        );

        $pledge = Pledge::createFor($targetMember, $campaign, [
            'amount' => $data['amount'],
            'frequency' => $data['frequency'],
            'notes' => $data['notes'] ?? null,
            'anonymous_display' => $campaign->allow_anonymous,
        ], 'member', Auth::id());

        PledgeAuditLog::record(
            $targetMember->id === $actingMember->id ? 'pledge.created_by_member' : 'pledge.created_on_behalf',
            $pledge,
            null,
            $pledge->toArray()
        );

        $pledge->load(['campaign', 'member']);

        return response()->json($this->pledgePayload($pledge, $actingMember), 201);
    }

    /**
     * The logged-in user's own pledges, plus any pledges they recorded on
     * behalf of someone else (their own is a subset; "made_for_self" in the
     * payload tells the client which is which).
     */
    public function index(Request $request)
    {
        $member = $this->currentMember($request);

        $query = Pledge::with(['campaign', 'member'])
            ->where(function ($q) use ($member, $request) {
                $q->where('member_id', $member->id)
                  ->orWhere('recorded_by', $request->user()->id);
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pledges = $query->orderByDesc('created_at')->paginate(15);

        return response()->json([
            'data' => collect($pledges->items())->map(fn ($p) => $this->pledgePayload($p, $member))->values(),
            'current_page' => $pledges->currentPage(),
            'last_page' => $pledges->lastPage(),
            'total' => $pledges->total(),
        ]);
    }

    public function show(Request $request, Pledge $pledge)
    {
        $member = $this->currentMember($request);

        abort_unless(
            $pledge->member_id === $member->id || $pledge->recorded_by === $request->user()->id,
            403,
            'You may only view your own pledges.'
        );

        $pledge->load(['campaign', 'member', 'contributions' => fn ($q) => $q->orderByDesc('payment_date')]);

        return response()->json($this->pledgePayload($pledge, $member, true));
    }

    /**
     * Printable pledge PDF - same view/QR/logo pattern as
     * MyPledgesController::downloadPdf(), returned as raw bytes instead of a
     * browser download response since the client here is a mobile app.
     */
    public function downloadPdf(Request $request, Pledge $pledge)
    {
        $member = $this->currentMember($request);

        abort_unless(
            $pledge->member_id === $member->id || $pledge->recorded_by === $request->user()->id,
            403,
            'You may only download your own pledges.'
        );

        $pledge->load(['campaign', 'member.church', 'contributions' => fn ($q) => $q->orderBy('payment_date')]);

        $scanUrl = route('pledge-scan.show', $pledge->id);
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(160)->generate($scanUrl);
        $qr = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        $logoPath = public_path('assets/images/logo/LW-LOGO.png');
        $logo = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $banner = null;
        if ($pledge->campaign && $pledge->campaign->banner_path) {
            $bannerPath = storage_path('app/public/' . $pledge->campaign->banner_path);
            if (file_exists($bannerPath)) {
                $mime = mime_content_type($bannerPath) ?: 'image/jpeg';
                $banner = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($bannerPath));
            }
        }

        $pdf = Pdf::loadView('portal.pledges.pdf.pledge', compact('pledge', 'qr', 'logo', 'banner'));

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $pledge->pledge_reference . '.pdf"',
        ]);
    }
}
