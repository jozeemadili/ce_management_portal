<?php

namespace App\Services;

use App\Models\ProgramAttendance;
use App\Models\ProgramAuditLog;
use App\Models\ProgramRegistration;
use App\Models\ProgramSession;
use Illuminate\Support\Carbon;

/**
 * QR check-in rules, shared by the web scan page and the mobile API:
 *  - the program must be active and the registration valid and paid (or free);
 *  - a special program only checks in on its own dates;
 *  - with sessions, check-in is per session: it opens
 *    ProgramSession::CHECK_IN_OPENS_BEFORE_MINUTES before the session starts
 *    and closes when it ends, and each person checks in once per session
 *    per day. Programs without sessions keep one check-in per day.
 */
class ProgramCheckIn
{
    /**
     * @return array{ok: bool, message: string, occurrence: ?\App\Models\ProgramOccurrence, session: ?ProgramSession}
     */
    public function evaluate(ProgramRegistration $registration, ?Carbon $now = null): array
    {
        $now = $now ?: now();
        $program = $registration->program;
        $fail = fn (string $message, $occurrence = null, $session = null) => [
            'ok' => false, 'message' => $message, 'occurrence' => $occurrence, 'session' => $session,
        ];

        if (!$program || $program->status === 'cancelled') {
            return $fail('This program has been cancelled. Check-in is not available.');
        }
        if ($program->status === 'draft') {
            return $fail('This program is not yet active.');
        }
        if ($registration->registration_status !== 'registered') {
            return $fail('This registration has been cancelled and is no longer valid.');
        }
        if (!$registration->isSettled()) {
            $due = $registration->amount_due !== null
                ? ' Balance: ' . $program->currency . ' ' . number_format($registration->balance()) . '.'
                : '';

            return $fail('Payment is required before check-in.' . $due);
        }

        if ($program->classification === 'special' && $program->start_date) {
            $start = $program->start_date->copy()->startOfDay();
            $end = ($program->end_date ?: $program->start_date)->copy()->endOfDay();

            if ($now->lt($start)) {
                return $fail('Check-in opens on ' . $start->format('d M Y') . ', when the program starts.');
            }
            if ($now->gt($end)) {
                return $fail('This program ended on ' . $end->format('d M Y') . '.');
            }
        }

        $sessions = $program->sessions()->get();
        $session = null;

        if ($sessions->isNotEmpty()) {
            $session = $sessions->first(fn (ProgramSession $s) => $s->isCheckInOpen($now));

            if (!$session) {
                $next = $sessions->first(fn (ProgramSession $s) => $s->startsOn($now)->gt($now));

                return $fail($next
                    ? "No session is open for check-in right now. Next: {$next->name} at " . substr($next->start_time, 0, 5)
                        . ' (check-in opens ' . ProgramSession::CHECK_IN_OPENS_BEFORE_MINUTES . ' minutes before).'
                    : "Today's sessions have ended.");
            }
        }

        $occurrence = $program->occurrenceForDate($now->toDateString());

        $already = ProgramAttendance::where('occurrence_id', $occurrence->id)
            ->where('registration_id', $registration->id)
            ->when($session, fn ($q) => $q->where('session_id', $session->id))
            ->first();

        if ($already) {
            return $fail(
                ($session ? "Already checked in for the {$session->name} today" : 'This registration has already been checked in for today')
                    . ' at ' . optional($already->checked_in_at)->format('H:i') . '.',
                $occurrence,
                $session
            );
        }

        return [
            'ok' => true,
            'message' => $session ? "Ready to check in for the {$session->name}." : 'Ready to check in.',
            'occurrence' => $occurrence,
            'session' => $session,
        ];
    }

    /**
     * Records the check-in if evaluate() allows it.
     *
     * @return array{ok: bool, message: string, attendance: ?ProgramAttendance, session: ?ProgramSession}
     */
    public function checkIn(ProgramRegistration $registration, ?int $recordedBy): array
    {
        $check = $this->evaluate($registration);
        if (!$check['ok']) {
            return ['ok' => false, 'message' => $check['message'], 'attendance' => null, 'session' => $check['session']];
        }

        $attendance = ProgramAttendance::create([
            'program_id' => $registration->program_id,
            'occurrence_id' => $check['occurrence']->id,
            'session_id' => optional($check['session'])->id,
            'member_id' => $registration->member_id,
            'registration_id' => $registration->id,
            'attendance_status' => 'present',
            'check_in_method' => 'qr',
            'checked_in_at' => now(),
            'recorded_by' => $recordedBy,
        ]);

        ProgramAuditLog::record('attendance.checked_in_qr', $attendance, null, $attendance->toArray());

        return [
            'ok' => true,
            'message' => $check['session'] ? "Checked in for the {$check['session']->name}." : 'Checked in successfully.',
            'attendance' => $attendance,
            'session' => $check['session'],
        ];
    }
}
