<?php

namespace App\Http\Controllers\API\Programs;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\ProgramAttendance;
use App\Models\ProgramAuditLog;
use App\Models\ProgramRegistration;
use Illuminate\Support\Facades\Auth;

class ProgramQrController extends Controller
{
    use AuthorizesPrograms;

    /**
     * Runs the full section-21 validation chain for a scanned registration
     * and returns [ok, message, occurrence|null]. Never throws - the scan
     * page always renders, with a clear reason when check-in isn't allowed.
     */
    private function validateScan(ProgramRegistration $registration): array
    {
        $program = $registration->program;

        if (!$program || $program->status === 'cancelled') {
            return [false, 'This program has been cancelled. Check-in is not available.', null];
        }

        if ($program->status === 'draft') {
            return [false, 'This program is not yet active.', null];
        }

        if ($registration->registration_status !== 'registered') {
            return [false, 'This registration has been cancelled and is no longer valid.', null];
        }

        if (!$program->isFree() && $registration->payment_status !== 'paid') {
            return [false, 'Payment is required before check-in. Payment status: ' . ucfirst($registration->payment_status) . '.', null];
        }

        $occurrence = $program->occurrenceForDate(now()->toDateString());

        $alreadyCheckedIn = ProgramAttendance::where('occurrence_id', $occurrence->id)
            ->where('registration_id', $registration->id)
            ->exists();

        if ($alreadyCheckedIn) {
            return [false, 'This registration has already been checked in for today.', $occurrence];
        }

        return [true, 'Ready to check in.', $occurrence];
    }

    /**
     * Landing page for the QR code printed on a registration's PDF - shows
     * the program + member at a glance and a "Check In" button that posts
     * to checkIn() below.
     */
    public function show(ProgramRegistration $registration)
    {
        $this->authorizeProgram('ATTENDANCE_SCAN_QR');

        $registration->load(['program', 'member.church', 'attendance']);

        [$ok, $message, $occurrence] = $this->validateScan($registration);

        return view('portal.programs.scan.show', compact('registration', 'ok', 'message'));
    }

    public function checkIn(ProgramRegistration $registration)
    {
        $this->authorizeProgram('ATTENDANCE_SCAN_QR');

        $registration->load('program');

        [$ok, $message, $occurrence] = $this->validateScan($registration);

        if (!$ok) {
            return back()->with('error', $message);
        }

        $attendance = ProgramAttendance::create([
            'program_id' => $registration->program_id,
            'occurrence_id' => $occurrence->id,
            'member_id' => $registration->member_id,
            'registration_id' => $registration->id,
            'attendance_status' => 'present',
            'check_in_method' => 'qr',
            'checked_in_at' => now(),
            'recorded_by' => Auth::id(),
        ]);

        ProgramAuditLog::record('attendance.checked_in_qr', $attendance, null, $attendance->toArray());

        return back()->with('success', 'Checked in successfully.');
    }
}
