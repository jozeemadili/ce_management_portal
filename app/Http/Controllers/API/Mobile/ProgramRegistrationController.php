<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Member;
use App\Models\Program;
use App\Models\ProgramAuditLog;
use App\Models\ProgramRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramRegistrationController extends Controller
{
    private function currentMember(Request $request): Member
    {
        $member = $request->user()->member;

        abort_unless($member, 403, 'No member record found for this user. Registering requires a linked member profile.');

        return $member;
    }

    /**
     * Church IDs the acting member may search/register within: their own
     * church plus its direct sub-churches. Same bounded scope used by the
     * mobile Pledges "pledge for someone else" search.
     */
    private function scopedChurchIds(Member $member): array
    {
        return Church::where('id', $member->church_id)
            ->orWhere('parent_church_id', $member->church_id)
            ->pluck('id')
            ->toArray();
    }

    private function registrationPayload(ProgramRegistration $registration, ?Member $viewer = null, bool $detailed = false): array
    {
        $program = $registration->program;
        $member = $registration->relationLoaded('member') ? $registration->member : null;

        $payload = [
            'id' => $registration->id,
            'reference' => $registration->registration_reference,
            'program_id' => $registration->program_id,
            'program' => optional($program)->name,
            'banner_url' => optional($program)->banner_path ? asset('storage/' . $program->banner_path) : null,
            'location' => optional($program)->location,
            'start_date' => optional(optional($program)->start_date)->toDateString(),
            'end_date' => optional(optional($program)->end_date)->toDateString(),
            'start_time' => optional($program)->start_time,
            'end_time' => optional($program)->end_time,
            'access_type' => optional($program)->access_type,
            'registration_fee' => (float) optional($program)->registration_fee,
            'currency' => optional($program)->currency,
            'registration_status' => $registration->registration_status,
            'payment_status' => $registration->payment_status,
            'registered_at' => optional($registration->registered_at)->toDateTimeString(),
            'made_for_self' => $viewer ? $registration->member_id === $viewer->id : null,
            'member' => $member ? [
                'id' => $member->id,
                'name' => trim($member->first_name . ' ' . $member->last_name),
                'church' => $member->relationLoaded('church') ? optional($member->church)->name : null,
            ] : null,
            'scan_url' => route('program-scan.show', $registration->id),
            // Same link the web confirmation/detail pages copy as the
            // "invitation link" - the portal's registration detail page.
            'share_url' => route('my-programs.show', $registration->id),
        ];

        if ($detailed) {
            $attendance = $registration->relationLoaded('attendance') ? $registration->attendance : null;
            $payload['checked_in'] = $attendance !== null;
            $payload['checked_in_at'] = $attendance ? optional($attendance->checked_in_at)->toDateTimeString() : null;
        }

        return $payload;
    }

    /**
     * Churches for the "Register Someone Else" church picker - every church,
     * same list the web registration desk modal offers.
     */
    public function churches()
    {
        return response()->json(
            Church::orderBy('name')->get(['id', 'name'])->values()
        );
    }

    /**
     * Member lookup for "register someone else". With church_id, searches
     * that church only - same as the web registration desk
     * (MyProgramRegistrationController::searchMembers()). Without it, falls
     * back to the acting member's own church + sub-churches.
     */
    public function searchMembers(Request $request)
    {
        $member = $this->currentMember($request);
        $search = trim((string) $request->get('q'));

        abort_if($search === '', 422, 'Provide a search term.');

        $query = Member::with('church')->where('member_type', 'member');

        if ($request->filled('church_id')) {
            $query->where('church_id', $request->church_id);
        } else {
            $query->whereIn('church_id', $this->scopedChurchIds($member));
        }

        $results = $query
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })
            ->limit(15)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => trim($m->first_name . ' ' . $m->last_name),
                'phone' => $m->phone,
                'church' => optional($m->church)->name,
            ]);

        return response()->json($results->values());
    }

    /**
     * Registers the acting member's own profile by default. Pass member_id
     * (an existing member of any church, picked via the church + member
     * search - same as the web registration desk) or
     * first_name/last_name/phone/church_id (a brand-new visitor, via
     * findOrCreateNewSoul) to register/invite someone else instead.
     */
    public function store(Request $request, Program $program)
    {
        $actingMember = $this->currentMember($request);

        abort_unless($program->classification === 'special', 422, 'Only special programs accept registration.');
        abort_unless($program->status === 'active', 422, 'This program is not currently open for registration.');

        if ($request->filled('member_id')) {
            $member = Member::where('member_type', 'member')->findOrFail($request->member_id);
        } elseif ($request->filled('first_name')) {
            $data = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'phone' => 'required|string|max:50',
                'church_id' => 'nullable|exists:churches,id',
            ]);

            $member = Member::findOrCreateNewSoul([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'phone' => $data['phone'],
            ], (int) ($data['church_id'] ?? $actingMember->church_id), Auth::id());
        } else {
            $member = $actingMember;
        }

        $attendeeName = trim($member->first_name . ' ' . $member->last_name);

        $already = ProgramRegistration::where('program_id', $program->id)
            ->where('member_id', $member->id)
            ->where('registration_status', 'registered')
            ->first();

        abort_if($already, 422, "{$attendeeName} is already registered for this program.");

        $registration = ProgramRegistration::createFor($member, $program, Auth::id());

        ProgramAuditLog::record('registration.created', $registration, null, $registration->toArray());

        $registration->load(['program', 'member.church']);

        return response()->json($this->registrationPayload($registration, $actingMember), 201);
    }

    /**
     * The logged-in user's own registrations, plus any they registered on
     * behalf of someone else (their own is a subset; "made_for_self" tells
     * the client which is which - mirrors API\Mobile\PledgeController::index()).
     */
    public function index(Request $request)
    {
        $member = $this->currentMember($request);

        $registrations = ProgramRegistration::with(['program', 'member.church'])
            ->where(function ($q) use ($member, $request) {
                $q->where('member_id', $member->id)
                  ->orWhere('registered_by', $request->user()->id);
            })
            ->orderByDesc('registered_at')
            ->paginate(15);

        return response()->json([
            'data' => collect($registrations->items())->map(fn ($r) => $this->registrationPayload($r, $member))->values(),
            'current_page' => $registrations->currentPage(),
            'last_page' => $registrations->lastPage(),
            'total' => $registrations->total(),
        ]);
    }

    public function show(Request $request, ProgramRegistration $registration)
    {
        $member = $this->currentMember($request);

        abort_unless(
            $registration->registered_by === $request->user()->id || $registration->member_id === $member->id,
            403,
            'You may only view registrations you made or your own.'
        );

        $registration->load(['program', 'attendance', 'member.church']);

        return response()->json($this->registrationPayload($registration, $member, true));
    }

    /**
     * Printable registration PDF - same view/QR/logo/banner pattern as the
     * web MyProgramRegistrationController::downloadPdf(), returned as raw
     * bytes instead of a browser download response. Unlike the web version
     * (deliberately open to any authenticated user, since front-desk staff
     * print for others), this is gated by the same ownership rule as show() -
     * mobile users shouldn't be able to fetch an arbitrary registration's
     * PDF by guessing ids.
     */
    public function downloadPdf(Request $request, ProgramRegistration $registration)
    {
        $member = $this->currentMember($request);

        abort_unless(
            $registration->registered_by === $request->user()->id || $registration->member_id === $member->id,
            403,
            'You may only download registrations you made or your own.'
        );

        $registration->load(['program', 'member.church']);

        $scanUrl = route('program-scan.show', $registration->id);
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(160)->generate($scanUrl);
        $qr = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        $logoPath = public_path('assets/images/logo/LW-LOGO.png');
        $logo = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $banner = null;
        if ($registration->program && $registration->program->banner_path) {
            $bannerPath = storage_path('app/public/' . $registration->program->banner_path);
            if (file_exists($bannerPath)) {
                $mime = mime_content_type($bannerPath) ?: 'image/jpeg';
                $banner = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($bannerPath));
            }
        }

        $pdf = Pdf::loadView('portal.programs.pdf.registration', compact('registration', 'qr', 'logo', 'banner'));

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $registration->registration_reference . '.pdf"',
        ]);
    }
}
