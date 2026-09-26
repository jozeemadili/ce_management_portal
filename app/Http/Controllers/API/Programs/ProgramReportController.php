<?php

namespace App\Http\Controllers\API\Programs;

use App\Exports\NewSoulsExport;
use App\Exports\ProgramAttendanceExport;
use App\Exports\ProgramRegistrationsExport;
use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Member;
use App\Models\Program;
use App\Models\ProgramAttendance;
use App\Models\ProgramRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ProgramReportController extends Controller
{
    use AuthorizesPrograms;

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

    private function scopedProgramIds()
    {
        $churchIds = $this->scopedChurchIds();

        $query = Program::query();
        if (!is_null($churchIds)) {
            $query->where(function ($q) use ($churchIds) {
                $q->where('scope', 'global')->orWhereIn('church_id', $churchIds);
            });
        }

        return $query->pluck('id');
    }

    public function index()
    {
        $this->authorizeProgram('PROGRAM_REPORTS_VIEW');

        return view('portal.programs.reports.index');
    }

    private function scopedRegistrationsQuery(Request $request)
    {
        $query = ProgramRegistration::with(['program', 'member.church', 'registeredBy'])
            ->whereIn('program_id', $this->scopedProgramIds());
        // Attendee type: first-time visitors (new souls) vs regular members.
        if (in_array($request->attendee_type, ['new_soul', 'member'], true)) {
            $query->whereHas('member', fn ($q) => $q->where('member_type', $request->attendee_type));
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('status')) {
            $query->where('registration_status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('from')) {
            $query->whereDate('registered_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('registered_at', '<=', $request->to);
        }

        return $query->orderByDesc('registered_at');
    }

    public function registrations(Request $request)
    {
        $this->authorizeProgram('PROGRAM_REPORTS_VIEW');

        $registrations = $this->scopedRegistrationsQuery($request)->paginate(15)->withQueryString();
        $programs = Program::whereIn('id', $this->scopedProgramIds())->orderBy('name')->get();

        return view('portal.programs.reports.registrations', compact('registrations', 'programs'));
    }

    public function exportRegistrations(Request $request)
    {
        $this->authorizeProgram('PROGRAM_REPORTS_EXPORT');

        $registrations = $this->scopedRegistrationsQuery($request)->get();

        return Excel::download(new ProgramRegistrationsExport($registrations), 'program-registrations-' . now()->format('Y-m-d_His') . '.xlsx');
    }

    private function scopedAttendanceQuery(Request $request)
    {
        $query = ProgramAttendance::with(['program', 'occurrence', 'member.church'])
            ->whereIn('program_id', $this->scopedProgramIds());

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('status')) {
            $query->where('attendance_status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereHas('occurrence', fn($q) => $q->whereDate('occurrence_date', '>=', $request->from));
        }

        if ($request->filled('to')) {
            $query->whereHas('occurrence', fn($q) => $q->whereDate('occurrence_date', '<=', $request->to));
        }

        return $query->orderByDesc('checked_in_at');
    }

    public function attendance(Request $request)
    {
        $this->authorizeProgram('PROGRAM_REPORTS_VIEW');

        $attendances = $this->scopedAttendanceQuery($request)->paginate(15)->withQueryString();
        $programs = Program::whereIn('id', $this->scopedProgramIds())->orderBy('name')->get();

        return view('portal.programs.reports.attendance', compact('attendances', 'programs'));
    }

    public function exportAttendance(Request $request)
    {
        $this->authorizeProgram('PROGRAM_REPORTS_EXPORT');

        $attendances = $this->scopedAttendanceQuery($request)->get();

        return Excel::download(new ProgramAttendanceExport($attendances), 'program-attendance-' . now()->format('Y-m-d_His') . '.xlsx');
    }

    private function scopedVisitorsQuery(Request $request)
    {
        $churchIds = $this->scopedChurchIds();

        $query = Member::newSouls()->with(['church', 'firstVisitProgram']);

        if (!is_null($churchIds)) {
            $query->whereIn('church_id', $churchIds);
        }

        if ($request->filled('status')) {
            $query->where('follow_up_status', $request->status);
        }

        return $query->orderByDesc('first_visit_date');
    }

    public function exportNewSouls(Request $request)
    {
        $this->authorizeProgram('PROGRAM_REPORTS_EXPORT');

        $visitors = $this->scopedVisitorsQuery($request)->get();

        return Excel::download(new NewSoulsExport($visitors), 'new-souls-' . now()->format('Y-m-d_His') . '.xlsx');
    }

    /**
     * Printable PDF attendance report for a single program: church logo,
     * program details, and the attendee table for a given date (defaults to
     * the program's own occurrences, most recent first if no date given).
     */
    public function pdfAttendance(Request $request, Program $program)
    {
        $this->authorizeProgram('PROGRAM_REPORTS_EXPORT');

        $date = $request->get('date');

        $occurrenceQuery = $program->occurrences()->orderByDesc('occurrence_date');
        if ($date) {
            $occurrenceQuery->whereDate('occurrence_date', $date);
        }
        $occurrence = $occurrenceQuery->first();

        abort_unless($occurrence, 404, 'No attendance has been recorded for this program yet.');

        $attendances = ProgramAttendance::with(['member.church'])
            ->where('occurrence_id', $occurrence->id)
            ->orderBy('attendance_status')
            ->get();

        $logoPath = public_path('assets/images/logo/LW-LOGO.png');
        $logo = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $summary = [
            'present' => $attendances->where('attendance_status', 'present')->count(),
            'absent' => $attendances->where('attendance_status', 'absent')->count(),
            'late' => $attendances->where('attendance_status', 'late')->count(),
            'excused' => $attendances->where('attendance_status', 'excused')->count(),
            'total' => $attendances->count(),
        ];

        $pdf = Pdf::loadView('portal.programs.pdf.attendance-report', compact('program', 'occurrence', 'attendances', 'logo', 'summary'));

        return $pdf->download('attendance-' . $program->id . '-' . $occurrence->occurrence_date->format('Y-m-d') . '.pdf');
    }
}
