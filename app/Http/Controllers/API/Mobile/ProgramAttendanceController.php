<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ProgramAttendance;
use App\Models\ProgramAuditLog;
use App\Models\ProgramRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramAttendanceController extends Controller
{
    /**
     * Same no-throw validation chain as the web
     * ProgramQrController::validateScan() - duplicated here rather than
     * shared, matching how this module already repeats small
     * controller-local helpers instead of introducing a cross-web/API
     * abstraction. Returns [ok, message, occurrence|null].
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

    private function scanPayload(ProgramRegistration $registration, bool $ok, string $message): array
    {
        $program = $registration->program;
        $member = $registration->member;

        return [
            'ok' => $ok,
            'message' => $message,
            'registration' => [
                'id' => $registration->id,
                'reference' => $registration->registration_reference,
                'registration_status' => $registration->registration_status,
                'payment_status' => $registration->payment_status,
            ],
            'program' => $program ? [
                'id' => $program->id,
                'name' => $program->name,
                'location' => $program->location,
            ] : null,
            'member' => $member ? [
                'id' => $member->id,
                'name' => trim($member->first_name . ' ' . $member->last_name),
                'church' => optional($member->church)->name,
            ] : null,
        ];
    }

    /**
     * Hit right after the app's camera scans a registration's QR code (the
     * printed/PDF QR encodes the web scan URL - the app extracts the
     * trailing numeric id from it and calls this with that id). Returns
     * whether check-in is currently allowed, and why not if it's blocked -
     * never a validation exception, matching the web scan page's behavior.
     */
    public function scan(Request $request, ProgramRegistration $registration)
    {
        $registration->load(['program', 'member.church']);

        [$ok, $message] = $this->validateScan($registration);

        return response()->json($this->scanPayload($registration, $ok, $message));
    }

    public function checkIn(Request $request, ProgramRegistration $registration)
    {
        $registration->load(['program', 'member.church']);

        [$ok, $message, $occurrence] = $this->validateScan($registration);

        if (!$ok) {
            return response()->json($this->scanPayload($registration, false, $message), 422);
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

        $payload = $this->scanPayload($registration, true, 'Checked in successfully.');
        $payload['checked_in_at'] = $attendance->checked_in_at->toDateTimeString();

        return response()->json($payload, 201);
    }
}
