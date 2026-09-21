<?php

namespace App\Http\Controllers\API\Programs;

use App\Exports\ProgramsExport;
use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\CellGroup;
use App\Models\Department;
use App\Models\Program;
use App\Models\ProgramAuditLog;
use App\Models\ProgramOccurrence;
use App\Models\ProgramRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProgramController extends Controller
{
    use AuthorizesPrograms;

    /**
     * Churches visible to the current user (their church + sub-churches, or
     * all churches if root / a pure ADMIN account with no member record).
     */
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

    private function scopedProgramsQuery(Request $request)
    {
        $churchIds = $this->scopedChurchIds();

        $query = Program::with(['church', 'department', 'cellGroup', 'creator'])->withCount(['registrations', 'attendances']);

        if (!is_null($churchIds)) {
            $query->where(function ($q) use ($churchIds) {
                $q->where('scope', 'global')->orWhereIn('church_id', $churchIds);
            });
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('classification')) {
            $query->where('classification', $request->classification);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('access_type')) {
            $query->where('access_type', $request->access_type);
        }

        return $query->orderByDesc('id');
    }

    public function index(Request $request)
    {
        $this->authorizeProgram('PROGRAMS_VIEW');

        $programs = $this->scopedProgramsQuery($request)->paginate(12)->withQueryString();

        $churchIds = $this->scopedChurchIds();
        $churches = is_null($churchIds)
            ? Church::orderBy('name')->get()
            : Church::whereIn('id', $churchIds)->orderBy('name')->get();

        $departments = is_null($churchIds)
            ? Department::orderBy('name')->get()
            : Department::whereIn('church_id', $churchIds)->orderBy('name')->get();

        $cellGroups = is_null($churchIds)
            ? CellGroup::orderBy('name')->get()
            : CellGroup::whereIn('church_id', $churchIds)->orderBy('name')->get();

        $allPrograms = $this->scopedProgramsQuery($request)->get();

        $stats = [
            'total' => $allPrograms->count(),
            'active' => $allPrograms->where('status', 'active')->count(),
            'recurring' => $allPrograms->where('classification', 'recurring')->count(),
            'special' => $allPrograms->where('classification', 'special')->count(),
        ];

        return view('portal.programs.index', compact('programs', 'churches', 'departments', 'cellGroups', 'stats'));
    }

    public function store(Request $request)
    {
        $this->authorizeProgram('PROGRAMS_CREATE');

        $data = $this->validateProgram($request);
        $data['created_by'] = Auth::id();

        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request->file('banner')->store('program-banners', 'public');
        }

        $program = Program::create($data);

        // Special (one-off) programs get exactly one occurrence up front,
        // matching their start date - recurring programs generate occurrences
        // on demand when attendance is first captured for a given date.
        if ($program->classification === 'special' && $program->start_date) {
            $program->occurrenceForDate($program->start_date->format('Y-m-d'));
        }

        ProgramAuditLog::record('program.created', $program, null, $program->toArray());

        return back()->with('success', 'Program created successfully.');
    }

    public function update(Request $request, Program $program)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $data = $this->validateProgram($request);
        $old = $program->toArray();

        if ($request->hasFile('banner')) {
            if ($program->banner_path) {
                Storage::disk('public')->delete($program->banner_path);
            }
            $data['banner_path'] = $request->file('banner')->store('program-banners', 'public');
        }

        $program->update($data);

        ProgramAuditLog::record('program.updated', $program, $old, $program->fresh()->toArray());

        return back()->with('success', 'Program updated successfully.');
    }

    private function validateProgram(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:service,meeting,program,event,course,crusade,conference,cell_meeting,other',
            'classification' => 'required|in:recurring,special',
            'scope' => 'required|in:global,church,department,cell',
            'church_id' => 'nullable|exists:churches,id',
            'department_id' => 'nullable|exists:departments,id',
            'cell_group_id' => 'nullable|exists:cell_groups,id',
            'organizer' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'recurrence_frequency' => 'nullable|in:daily,weekly,monthly,custom',
            'recurrence_days' => 'nullable|array',
            'access_type' => 'required|in:free,paid',
            'registration_fee' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'status' => 'required|in:draft,active,completed,cancelled',
            'banner' => 'nullable|image|max:4096',
        ]);

        $data['currency'] = $data['currency'] ?? 'TZS';
        $data['registration_fee'] = $data['access_type'] === 'paid' ? ($data['registration_fee'] ?? 0) : 0;
        $data['church_id'] = $data['scope'] === 'church' ? $data['church_id'] : null;
        $data['department_id'] = $data['scope'] === 'department' ? $data['department_id'] : null;
        $data['cell_group_id'] = $data['scope'] === 'cell' ? $data['cell_group_id'] : null;
        $data['is_recurring'] = $data['classification'] === 'recurring';
        $data['qr_enabled'] = $request->boolean('qr_enabled');

        if (!$data['is_recurring']) {
            $data['recurrence_frequency'] = null;
            $data['recurrence_days'] = null;
        }

        unset($data['banner']);

        return $data;
    }

    public function setStatus(Request $request, Program $program, string $status)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        abort_unless(in_array($status, ['draft', 'active', 'completed', 'cancelled']), 422, 'Invalid status.');

        $old = ['status' => $program->status];
        $program->update(['status' => $status]);

        ProgramAuditLog::record('program.status_changed', $program, $old, ['status' => $status]);

        return back()->with('success', 'Program status updated to ' . ucfirst($status) . '.');
    }

    public function show(Program $program)
    {
        $this->authorizeProgram('PROGRAMS_VIEW');

        $registrations = $program->registrations()->with('member')->orderByDesc('created_at')->get();

        $attendanceStats = [
            'registered' => $registrations->count(),
            'attended' => $program->attendances()->where('attendance_status', 'present')->count()
                + $program->attendances()->where('attendance_status', 'late')->count(),
            'absent' => $program->attendances()->where('attendance_status', 'absent')->count(),
            'new_souls' => $program->attendances()->whereHas('member', fn($q) => $q->where('member_type', 'new_soul'))->count(),
        ];
        $attendanceStats['rate'] = $attendanceStats['registered'] > 0
            ? round(($attendanceStats['attended'] / $attendanceStats['registered']) * 100, 2)
            : 0;

        $revenue = $registrations->where('payment_status', 'paid')->sum('amount_paid');

        $occurrences = $program->occurrences()->orderByDesc('occurrence_date')->get();

        $recentActivity = ProgramAuditLog::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->with('actor')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('portal.programs.show', compact(
            'program', 'registrations', 'attendanceStats', 'revenue', 'occurrences', 'recentActivity'
        ));
    }

    public function export(Request $request)
    {
        $this->authorizeProgram('PROGRAM_REPORTS_VIEW');

        $programs = $this->scopedProgramsQuery($request)->get();

        return Excel::download(new ProgramsExport($programs), 'programs-' . now()->format('Y-m-d_His') . '.xlsx');
    }
}
