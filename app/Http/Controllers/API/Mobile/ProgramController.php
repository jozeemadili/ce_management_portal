<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramRegistration;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Special, active, not-yet-ended programs visible to the member - JSON
     * mirror of MyProgramRegistrationController::browse(), plus an
     * already_registered flag per program computed from the member's own
     * registered registrations.
     */
    public function index(Request $request)
    {
        $member = $request->user()->member;

        $programsQuery = Program::where('classification', 'special')
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
            });

        if ($member) {
            $programsQuery->visibleToMember($member);
        }

        $programs = $programsQuery->orderBy('start_date')->get();

        $myRegisteredIds = $member
            ? ProgramRegistration::where('member_id', $member->id)
                ->where('registration_status', 'registered')
                ->pluck('program_id')
                ->all()
            : [];

        return response()->json($programs->map(fn ($program) => [
            'id' => $program->id,
            'name' => $program->name,
            'description' => $program->description,
            'banner_url' => $program->banner_path ? asset('storage/' . $program->banner_path) : null,
            'category' => $program->category,
            'location' => $program->location,
            'start_date' => optional($program->start_date)->toDateString(),
            'end_date' => optional($program->end_date)->toDateString(),
            'start_time' => $program->start_time,
            'access_type' => $program->access_type,
            'registration_fee' => (float) $program->registration_fee,
            'currency' => $program->currency,
            'already_registered' => in_array($program->id, $myRegisteredIds),
        ])->values());
    }
}
