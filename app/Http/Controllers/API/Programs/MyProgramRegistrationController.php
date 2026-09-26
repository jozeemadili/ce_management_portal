<?php

namespace App\Http\Controllers\API\Programs;

use App\Exports\ProgramVisitorTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\MembersImport;
use App\Models\Church;
use App\Models\Member;
use App\Models\Program;
use App\Models\ProgramAuditLog;
use App\Models\ProgramRegistration;
use App\Services\ProgramVisitorBulkRegistrar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class MyProgramRegistrationController extends Controller
{
    /**
     * Special programs open for registration - a walk-in registration desk,
     * not pure self-service: any authenticated user (front desk, usher, or
     * a member browsing for themselves) can register an attendee here,
     * whether they're an existing church member or a first-time visitor
     * (who becomes a "new soul" member automatically). Visibility is scoped
     * to the logged-in user's own church hierarchy when they have a linked
     * member profile; otherwise (e.g. an ADMIN account with no member
     * record) every active special program is shown, matching the
     * unrestricted pattern used everywhere else in this module.
     */
    public function browse()
    {
        $currentMember = Auth::user()->member;

        $programsQuery = Program::where('classification', 'special')
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
            });

        if ($currentMember) {
            $programsQuery->visibleToMember($currentMember);
        }

        $programs = $programsQuery->orderBy('start_date')->get();

        $myRegisteredIds = $currentMember
            ? ProgramRegistration::where('member_id', $currentMember->id)->where('registration_status', 'registered')->pluck('program_id')
            : collect();

        $churches = Church::orderBy('name')->get();

        return view('portal.programs.registrations.browse', compact('programs', 'myRegisteredIds', 'churches', 'currentMember'));
    }

    /**
     * Registers either an existing real member (picked via the Select2
     * search, sent as member_id) or a first-time visitor (sent as
     * first_name/last_name/phone/church_id, resolved through
     * findOrCreateNewSoul - matched by phone if they already exist,
     * created as a new_soul-tagged member otherwise).
     */
    public function store(Request $request, Program $program)
    {
        abort_unless($program->classification === 'special', 422, 'Only special programs accept registration.');
        abort_unless($program->status === 'active', 422, 'This program is not currently open for registration.');

        if ($request->filled('member_id')) {
            $member = Member::where('member_type', 'member')->findOrFail($request->member_id);
        } else {
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

        return view('portal.programs.registrations.confirmation', compact('registration', 'program'));
    }

    /**
     * AJAX member search for the "Existing Church Member" mode of the
     * registration modal - scoped to a church, real members only (a new
     * soul isn't picked here; they go through the visitor path instead).
     */
    public function searchMembers(Request $request)
    {
        $search = trim((string) $request->get('q'));

        $query = Member::where('member_type', 'member')->with('church')->limit(15);

        if ($request->filled('church_id')) {
            $query->where('church_id', $request->church_id);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $results = $query->get()->map(fn($m) => [
            'id' => $m->id,
            'text' => trim($m->first_name . ' ' . $m->last_name)
                . ($m->phone ? ' — ' . $m->phone : '')
                . (optional($m->church)->name ? ' (' . $m->church->name . ')' : ''),
        ]);

        return response()->json(['results' => $results]);
    }

    /**
     * "People I've invited" - every registration the logged-in user
     * personally submitted through the registration desk, whether for
     * themselves or someone else.
     */
    public function index()
    {
        $registrations = ProgramRegistration::with(['program', 'member.church'])
            ->where('registered_by', Auth::id())
            ->orderByDesc('registered_at')
            ->paginate(10);

        return view('portal.programs.registrations.my', compact('registrations'));
    }

    public function show(ProgramRegistration $registration)
    {
        $ownMember = Auth::user()->member;

        abort_unless(
            $registration->registered_by === Auth::id() || ($ownMember && $registration->member_id === $ownMember->id),
            403,
            'You may only view registrations you made or your own.'
        );

        $registration->load(['program', 'attendance', 'member.church']);

        $scanUrl = route('program-scan.show', $registration->id);
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->generate($scanUrl);
        $qr = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        $bannerUrl = ($registration->program && $registration->program->banner_path)
            ? asset('storage/' . $registration->program->banner_path)
            : null;

        return view('portal.programs.registrations.detail', compact('registration', 'qr', 'bannerUrl'));
    }

    /**
     * Printable registration PDF with a QR code linking to the scan/check-in
     * page - same base64-embedding approach proven for the Pledges PDF
     * (dompdf drops inline <svg>, and local files must be data URIs).
     */
    public function downloadPdf(ProgramRegistration $registration)
    {
        // Any authenticated user may download a registration PDF - the
        // registration desk (browse()/store()) is itself open to anyone
        // logged in, including front-desk staff registering a walk-in who
        // isn't their own member record, so ownership can't be the gate
        // here. Every route in this module already requires auth.
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

        return $pdf->download($registration->registration_reference . '.pdf');
    }

    /* =================================================================
     | EXCEL UPLOAD OF FIRST-TIME VISITORS ("Register Someone Else")
     | Step 1 (preview) stores the file and reports what would happen;
     | step 2 re-checks the stored file and registers everyone for the
     | program. New people become new souls of the church chosen in the
     | registration modal.
     |=================================================================*/

    private function visitorUploadPath(string $token): string
    {
        return 'visitor-imports/' . Auth::id() . '/' . $token;
    }

    private function readVisitorRows(string $path): array
    {
        $sheets = Excel::toArray(new MembersImport, $path, 'local');

        return $sheets[0] ?? [];
    }

    private function assertOpenForRegistration(Program $program): void
    {
        abort_unless($program->classification === 'special', 422, 'Only special programs accept registration.');
        abort_unless($program->status === 'active', 422, 'This program is not currently open for registration.');
    }

    public function visitorTemplate()
    {
        return Excel::download(new ProgramVisitorTemplateExport, 'first-time-visitors-template.xlsx');
    }

    public function previewVisitors(Request $request, Program $program, ProgramVisitorBulkRegistrar $registrar)
    {
        $this->assertOpenForRegistration($program);

        $data = $request->validate([
            'church_id' => 'required|exists:churches,id',
            'file' => 'required|file|max:5120|mimes:xlsx,xls,csv,txt',
        ], [
            'church_id.required' => 'Select the church these visitors belong to first.',
            'file.mimes' => 'Upload an Excel file (.xlsx or .xls) or a CSV file.',
        ]);
        $church = Church::findOrFail($data['church_id']);

        $extension = strtolower($request->file('file')->getClientOriginalExtension() ?: 'xlsx');
        $token = Str::uuid() . '.' . (in_array($extension, ['xlsx', 'xls', 'csv']) ? $extension : 'xlsx');
        $path = $this->visitorUploadPath($token);

        // One pending upload per user: a new check replaces any earlier file
        // that was checked but never registered.
        Storage::disk('local')->delete(Storage::disk('local')->files(dirname($path)));
        Storage::disk('local')->putFileAs(dirname($path), $request->file('file'), $token);

        try {
            $rows = $this->readVisitorRows($path);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            abort(422, 'This file could not be read. Download the template and fill it in.');
        }

        $firstRow = $rows[0] ?? [];
        if (!array_key_exists('first_name', $firstRow) || !array_key_exists('phone', $firstRow)) {
            Storage::disk('local')->delete($path);
            abort(422, 'The file is missing the template columns (First Name, Last Name, Phone). Download the template and fill it in.');
        }

        $result = $registrar->analyse($rows, $program);

        if ($result['total'] > ProgramVisitorBulkRegistrar::MAX_ROWS) {
            Storage::disk('local')->delete($path);
            abort(422, 'A file can hold at most ' . ProgramVisitorBulkRegistrar::MAX_ROWS . ' visitors. Split it into smaller files.');
        }

        return response()->json([
            'token' => $token,
            'program' => $program->name,
            'church' => $church->name,
            'total_rows' => $result['total'],
            'new_count' => count($result['new']),
            'register_count' => count($result['new']) + count($result['existing']),
            'existing' => collect($result['existing'])->map(fn ($r) => ['row' => $r['row'], 'name' => $r['name'], 'note' => $r['note']])->values(),
            'skipped' => $result['skipped'],
        ]);
    }

    public function registerVisitors(Request $request, Program $program, ProgramVisitorBulkRegistrar $registrar)
    {
        $this->assertOpenForRegistration($program);

        $data = $request->validate([
            'church_id' => 'required|exists:churches,id',
            'token' => ['required', 'regex:/^[0-9a-f-]{36}\.(xlsx|xls|csv)$/'],
        ]);
        $church = Church::findOrFail($data['church_id']);
        $path = $this->visitorUploadPath($data['token']);

        if (!Storage::disk('local')->exists($path)) {
            return redirect()->route('my-programs.browse')->withErrors('The uploaded file has expired. Please upload it again.');
        }

        // Re-checked rather than trusting the preview: someone may have been
        // registered at the desk in between.
        $result = $registrar->analyse($this->readVisitorRows($path), $program);
        $count = $registrar->register($result, $program, $church, Auth::id());
        Storage::disk('local')->delete($path);

        $newCount = count($result['new']);
        $message = "{$count} " . ($count === 1 ? 'person' : 'people') . " registered for {$program->name}";
        $message .= $newCount ? " ({$newCount} new first-time " . ($newCount === 1 ? 'visitor' : 'visitors') . " added to {$church->name})." : '.';
        if ($skipped = count($result['skipped'])) {
            $message .= " {$skipped} row" . ($skipped === 1 ? ' was' : 's were') . ' skipped.';
        }

        return redirect()->route('my-programs.browse')->with('success', $message);
    }
}
