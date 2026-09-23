<?php

namespace App\Http\Controllers\Api\Mobile;

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

        $payload = [
            'id' => $registration->id,
            'reference' => $registration->registration_reference,
            'program_id' => $registration->program_id,
            'program' => optional($program)->name,
            'banner_url' => optional($program)->banner_path ? asset('storage/' . $program->banner_path) : null,
            'location' => optional($program)->location,
            'start_date' => optional(optional($program)->start_date)->toDateString(),
            'start_time' => optional($program)->start_time,
            'registration_status' => $registration->registration_status,
            'payment_status' => $registration->payment_status,
            'registered_at' => optional($registration->registered_at)->toDateTimeString(),
            'made_for_self' => $viewer ? $registration->member_id === $viewer->id : null,
            'member' => $registration->relationLoaded('member') && $registration->member ? [
                'id' => $registration->member->id,
                'name' => trim($registration->member->first_name . ' ' . $registration->member->last_name),
            ] : null,
            'scan_url' => route('program-scan.show', $registration->id),
        ];

        if ($detailed) {
            $attendance = $registration->relationLoaded('attendance') ? $registration->attendance : null;
            $payload['checked_in'] = $attendance !== null;
            $payload['checked_in_at'] = $attendance ? optional($attendance->checked_in_at)->toDateTimeString() : null;
        }

        return $payload;
    }

    /**
     * Member lookup for "register someone else" - scoped to the acting
     * member's own church + sub-churches, unlike the web's staff-facing
     * MyProgramRegistrationController::searchMembers() which takes an
     * arbitrary church_id.
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
     * (an existing member, scope-checked against the acting member's own
     * church) or first_name/last_name/phone/church_id (a brand-new visitor,
     * via findOrCreateNewSoul) to register/invite someone else instead.
     */
    public function store(Request $request, Program $program)
    {
        $actingMember = $this->currentMember($request);

        abort_unless($program->classification === 'special', 422, 'Only special programs accept registration.');
        abort_unless($program->status === 'active', 422, 'This program is not currently open for registration.');

        if ($request->filled('member_id')) {
            $member = Member::where('member_type', 'member')->findOrFail($request->member_id);

            abort_unless(
                in_array($member->church_id, $this->scopedChurchIds($actingMember)),
                403,
                'You may only register members of your own church.'
            );
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

        $registration->load(['program', 'member']);

        return response()->json($this->registrationPayload($registration, $actingMember), 201);
    }

    /**
     * The logged-in user's own registrations, plus any they registered on
     * behalf of someone else (their own is a subset; "made_for_self" tells
     * the client which is which - mirrors Api\Mobile\PledgeController::index()).
     */
    public function index(Request $request)
    {
        $member = $this->currentMember($request);

        $registrations = ProgramRegistration::with(['program', 'member'])
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
