<?php

namespace App\Http\Controllers\API\Pledges;

use App\Http\Controllers\Controller;
use App\Models\Pledge;
use App\Models\PledgeAuditLog;
use App\Models\PledgeCampaign;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyPledgesController extends Controller
{
    private function currentMember()
    {
        $member = Auth::user()->member;

        abort_unless($member, 403, 'No member record found for this user. Pledging requires a linked member profile.');

        return $member;
    }

    /**
     * Active campaigns available to the logged-in member: global campaigns,
     * or campaigns specific to their own church.
     */
    public function browse()
    {
        $member = $this->currentMember();

        $campaigns = PledgeCampaign::visibleToMember($member)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get();

        return view('portal.pledges.my.browse', compact('campaigns'));
    }

    public function store(Request $request)
    {
        $member = $this->currentMember();

        $data = $request->validate([
            'campaign_id' => 'required|exists:pledge_campaigns,id',
            'amount' => 'required|numeric|min:1',
            'frequency' => 'required|in:one_time,weekly,monthly,custom',
            'notes' => 'nullable|string',
        ]);

        $campaign = PledgeCampaign::findOrFail($data['campaign_id']);

        abort_unless($campaign->status === 'active', 422, 'This campaign is not currently accepting pledges.');
        abort_unless(
            $campaign->scope === 'global' || $campaign->church_id === $member->church_id,
            403,
            'This campaign is not available to your church.'
        );

        // Member pledges are always anonymous on the live/public screen - not
        // a per-pledge choice the member makes - unless the campaign itself
        // has anonymous pledging disabled entirely.
        $pledge = Pledge::createFor($member, $campaign, [
            'amount' => $data['amount'],
            'frequency' => $data['frequency'],
            'notes' => $data['notes'] ?? null,
            'anonymous_display' => $campaign->allow_anonymous,
        ], 'member');

        PledgeAuditLog::record('pledge.created_by_member', $pledge, null, $pledge->toArray());

        return view('portal.pledges.my.confirmation', compact('pledge', 'campaign'));
    }

    /**
     * The member's own pledge history.
     */
    public function index(Request $request)
    {
        $member = $this->currentMember();

        $query = Pledge::with('campaign')->where('member_id', $member->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pledges = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('portal.pledges.my.index', compact('pledges'));
    }

    public function show(Pledge $pledge)
    {
        $member = $this->currentMember();

        abort_unless($pledge->member_id === $member->id, 403, 'You may only view your own pledges.');

        $pledge->load(['campaign', 'contributions' => fn($q) => $q->orderByDesc('payment_date')]);

        return view('portal.pledges.my.show', compact('pledge'));
    }

    /**
     * Printable pledge PDF with a QR code linking to the scan page - either
     * the pledge's own member, or staff with view access (e.g. printing a
     * physical pledge card for someone at an event).
     */
    public function downloadPdf(Pledge $pledge)
    {
        $member = Auth::user()->member;
        $isOwner = $member && $pledge->member_id === $member->id;

        abort_unless($isOwner || Auth::user()->role === 'ADMIN' || Auth::user()->hasPermission('PLEDGES_VIEW'), 403);

        $pledge->load(['campaign', 'member.church', 'contributions' => fn($q) => $q->orderBy('payment_date')]);

        $scanUrl = route('pledge-scan.show', $pledge->id);
        // dompdf doesn't reliably render inline <svg> markup, but it does
        // support SVG via a normal <img src="data:image/svg+xml;base64,...">
        // - Imagick (needed for PNG output) isn't installed here, so SVG is
        // the only backend available, and this is how to make it visible.
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(160)->generate($scanUrl);
        $qr = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        // Embed images as base64 data URIs - the most reliable way to get
        // local files into a dompdf render regardless of chroot/remote config.
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

        return $pdf->download($pledge->pledge_reference . '.pdf');
    }
}
