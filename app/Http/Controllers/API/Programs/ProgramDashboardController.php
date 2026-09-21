<?php

namespace App\Http\Controllers\API\Programs;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Member;
use App\Models\Program;
use App\Models\ProgramAttendance;
use App\Models\ProgramOccurrence;
use App\Models\ProgramRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgramDashboardController extends Controller
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

    private function scopedProgramsBase()
    {
        $churchIds = $this->scopedChurchIds();

        $query = Program::query();
        if (!is_null($churchIds)) {
            $query->where(function ($q) use ($churchIds) {
                $q->where('scope', 'global')->orWhereIn('church_id', $churchIds);
            });
        }

        return $query;
    }

    /**
     * "What is happening today?" - programs whose schedule includes today:
     * recurring programs whose recurrence_days includes today's weekday, and
     * special programs currently within their start/end date range.
     */
    private function todaysPrograms()
    {
        $today = now();
        $todayName = strtolower($today->format('l'));
        $todayDate = $today->toDateString();

        return $this->scopedProgramsBase()->where('status', 'active')
            ->where(function ($q) use ($todayName, $todayDate) {
                $q->where(function ($q2) use ($todayName) {
                    $q2->where('classification', 'recurring')
                       ->whereJsonContains('recurrence_days', $todayName);
                })->orWhere(function ($q2) use ($todayDate) {
                    $q2->where('classification', 'special')
                       ->whereDate('start_date', '<=', $todayDate)
                       ->where(function ($q3) use ($todayDate) {
                           $q3->whereDate('end_date', '>=', $todayDate)->orWhereNull('end_date');
                       });
                });
            })
            ->get();
    }

    public function index()
    {
        $this->authorizeProgram('PROGRAMS_VIEW');

        $today = now()->toDateString();

        $todayPrograms = $this->todaysPrograms();
        $todayProgramIds = $todayPrograms->pluck('id');

        $todayOccurrenceIds = ProgramOccurrence::whereIn('program_id', $todayProgramIds)
            ->whereDate('occurrence_date', $today)
            ->pluck('id');

        $liveAttendanceCount = ProgramAttendance::whereIn('occurrence_id', $todayOccurrenceIds)
            ->where('attendance_status', 'present')
            ->count();

        $upcomingPrograms = $this->scopedProgramsBase()
            ->where('classification', 'special')
            ->where('status', 'active')
            ->whereDate('start_date', '>', $today)
            ->orderBy('start_date')
            ->limit(6)
            ->get();

        $churchIds = $this->scopedChurchIds();
        $visitorsBase = is_null($churchIds) ? Member::whereNotNull('first_visit_date') : Member::whereNotNull('first_visit_date')->whereIn('church_id', $churchIds);
        $newSoulsToday = (clone $visitorsBase)->whereDate('first_visit_date', $today)->count();

        $scopedProgramIds = $this->scopedProgramsBase()->pluck('id');
        $registrationsToday = ProgramRegistration::whereIn('program_id', $scopedProgramIds)
            ->whereDate('registered_at', $today)
            ->count();

        $stats = [
            'today_programs' => $todayPrograms->count(),
            'live_attendance' => $liveAttendanceCount,
            'new_souls_today' => $newSoulsToday,
            'registrations_today' => $registrationsToday,
            'total_programs' => $this->scopedProgramsBase()->count(),
            'active_programs' => $this->scopedProgramsBase()->where('status', 'active')->count(),
        ];

        // 7-day attendance trend across all scoped programs
        $since = now()->subDays(6)->startOfDay();
        $trend = ProgramAttendance::join('program_occurrences', 'program_attendances.occurrence_id', '=', 'program_occurrences.id')
            ->whereIn('program_attendances.program_id', $scopedProgramIds)
            ->where('program_attendances.attendance_status', 'present')
            ->where('program_occurrences.occurrence_date', '>=', $since->toDateString())
            ->select('program_occurrences.occurrence_date', DB::raw('count(*) as total'))
            ->groupBy('program_occurrences.occurrence_date')
            ->orderBy('program_occurrences.occurrence_date')
            ->get()
            ->pluck('total', 'occurrence_date');

        $recentActivity = \App\Models\ProgramAuditLog::with('actor')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('portal.programs.dashboard', compact(
            'stats', 'todayPrograms', 'upcomingPrograms', 'trend', 'recentActivity'
        ));
    }
}
