<?php

namespace App\Http\Controllers\API\Programs;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Program;
use App\Models\ProgramAttendance;
use App\Models\ProgramAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramAttendanceController extends Controller
{
    use AuthorizesPrograms;

    /**
     * Members eligible for this program's occurrence, per its scope: every
     * member of the church (church scope), only members in the department
     * (department scope), only members in the cell (cell scope), or - for
     * a global program - every member visible to the current user under the
     * normal hierarchy rules.
     */
    private function eligibleMembersQuery(Program $program)
    {
        // Only the real congregation - a new soul only joins the regular
        // roll once promoted to member_type=member.
        $base = Member::where('member_type', 'member');

        if ($program->scope === 'church' && $program->church_id) {
            return $base->where('church_id', $program->church_id);
        }

        if ($program->scope === 'department' && $program->department_id) {
            return $base->whereHas('departments', fn($q) => $q->where('departments.id', $program->department_id));
        }

        if ($program->scope === 'cell' && $program->cell_group_id) {
            return $base->whereHas('cell_groups', fn($q) => $q->where('cell_groups.id', $program->cell_group_id));
        }

        // Global: everyone the current user can see under the standard
        // church-hierarchy visibility rule used across every other module.
        $userMember = Auth::user()->member;
        if (!$userMember) {
            return $base; // ADMIN with no member record: unrestricted
        }

        $userChurch = \App\Models\Church::find($userMember->church_id);
        if (!$userChurch || is_null($userChurch->parent_church_id)) {
            return $base;
        }

        $churchIds = \App\Models\Church::where('id', $userChurch->id)
            ->orWhere('parent_church_id', $userChurch->id)
            ->pluck('id');

        return $base->whereIn('church_id', $churchIds);
    }

    public function capture(Request $request, Program $program)
    {
        $this->authorizeProgram('ATTENDANCE_RECORD');

        $date = $request->get('date', now()->format('Y-m-d'));
        $occurrence = $program->occurrenceForDate($date);

        $membersQuery = $this->eligibleMembersQuery($program);

        if ($request->filled('q')) {
            $search = $request->q;
            $membersQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $members = $membersQuery->orderBy('first_name')->get();

        $existingAttendance = ProgramAttendance::where('occurrence_id', $occurrence->id)
            ->whereNotNull('member_id')
            ->pluck('attendance_status', 'member_id');

        $visitorAttendance = ProgramAttendance::where('occurrence_id', $occurrence->id)
            ->whereHas('member', fn($q) => $q->where('member_type', 'new_soul'))
            ->with('member')
            ->get();

        return view('portal.programs.attendance.capture', compact(
            'program', 'occurrence', 'date', 'members', 'existingAttendance', 'visitorAttendance'
        ));
    }

    public function save(Request $request, Program $program)
    {
        $this->authorizeProgram('ATTENDANCE_RECORD');

        $data = $request->validate([
            'date' => 'required|date',
            'attendance' => 'nullable|array',
            'attendance.*' => 'in:present,absent,late,excused',
        ]);

        abort_unless($program->status !== 'cancelled', 422, 'This program has been cancelled and cannot accept attendance.');

        $occurrence = $program->occurrenceForDate($data['date']);

        foreach ($data['attendance'] ?? [] as $memberId => $status) {
            ProgramAttendance::updateOrCreate(
                ['occurrence_id' => $occurrence->id, 'member_id' => $memberId],
                [
                    'program_id' => $program->id,
                    'attendance_status' => $status,
                    'check_in_method' => 'manual',
                    'checked_in_at' => now(),
                    'recorded_by' => Auth::id(),
                ]
            );
        }

        ProgramAuditLog::record('attendance.recorded', $occurrence, null, ['count' => count($data['attendance'] ?? [])]);

        return back()->with('success', 'Attendance saved for ' . \Illuminate\Support\Carbon::parse($data['date'])->format('d M Y') . '.');
    }

    /**
     * Inline "record a first-time visitor" from the attendance screen -
     * finds-or-creates the visitor (so a returning visitor is recognized
     * instead of duplicated) and marks them present for this occurrence.
     */
    public function addVisitor(Request $request, Program $program)
    {
        $this->authorizeProgram('NEW_SOULS_CREATE');

        $data = $request->validate([
            'date' => 'required|date',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'gender' => 'nullable|string|max:20',
            'invited_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $churchId = $program->church_id ?? Auth::user()->member?->church_id;

        if (!$churchId) {
            $churchId = \App\Models\Church::whereNull('parent_church_id')->value('id');
        }

        abort_unless($churchId, 422, 'Unable to determine the church for this visitor.');

        $occurrence = $program->occurrenceForDate($data['date']);

        $visitor = Member::findOrCreateNewSoul([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'invited_by' => $data['invited_by'] ?? null,
            'notes' => $data['notes'] ?? null,
            'first_visit_program_id' => $program->id,
            'first_visit_date' => $data['date'],
        ], $churchId, Auth::id());

        $isNewVisitor = $visitor->wasRecentlyCreated;

        ProgramAttendance::updateOrCreate(
            ['occurrence_id' => $occurrence->id, 'member_id' => $visitor->id],
            [
                'program_id' => $program->id,
                'attendance_status' => 'present',
                'check_in_method' => 'manual',
                'checked_in_at' => now(),
                'recorded_by' => Auth::id(),
            ]
        );

        ProgramAuditLog::record($isNewVisitor ? 'new_soul.created' : 'new_soul.returned', $visitor, null, $visitor->toArray());

        return back()->with('success', ($isNewVisitor ? 'New soul' : 'Returning visitor') . " {$visitor->first_name} {$visitor->last_name} recorded.");
    }
}
