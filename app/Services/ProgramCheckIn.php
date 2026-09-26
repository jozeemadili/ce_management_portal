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
 *  - with sessions, whoever scans picks which of today's sessions to check
 *    the person into (the one running now is suggested); each person checks
 *    in once per session per day. Programs without sessions keep one
 *    check-in per day.
 */
class ProgramCheckIn
{
    /**
     * @return array{
     *   ok: bool, message: string,
     *   occurrence: ?\App\Models\ProgramOccurrence,
     *   session: ?ProgramSession,
     *   sessions: array<int, array{session: ProgramSession, checked_in_at: ?Carbon, open_now: bool}>
     * }
     */
    public function evaluate(ProgramRegistration $registration, ?Carbon $now = null): array
    {
        $now = $now ?: now();
        $program = $registration->program;
        $fail = fn (string $message, $occurrence = null, array $sessions = []) => [
            'ok' => false, 'message' => $message, 'occurrence' => $occurrence, 'session' => null, 'sessions' => $sessions,
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

        $occurrence = $program->occurrenceForDate($now->toDateString());
        $checkIns = ProgramAttendance::where('occurrence_id', $occurrence->id)
            ->where('registration_id', $registration->id)
            ->get();

        $sessions = $program->sessions()->get();

        if ($sessions->isEmpty()) {
            $already = $checkIns->first();
            if ($already) {
                return $fail('This registration has already been checked in for today at ' . optional($already->checked_in_at)->format('H:i') . '.', $occurrence);
            }

            return ['ok' => true, 'message' => 'Ready to check in.', 'occurrence' => $occurrence, 'session' => null, 'sessions' => []];
        }

        $list = $sessions->map(fn (ProgramSession $s) => [
            'session' => $s,
            'checked_in_at' => optional($checkIns->firstWhere('session_id', $s->id))->checked_in_at,
            'open_now' => $s->isCheckInOpen($now),
        ])->all();

        $available = collect($list)->whereNull('checked_in_at');
        if ($available->isEmpty()) {
            return $fail("Already checked in for all of today's sessions.", $occurrence, $list);
        }

        // Suggest the session running now, else the next one today, else the
        // first one not yet checked in (e.g. a late arrival).
        $suggested = $available->firstWhere('open_now', true)
            ?? $available->first(fn ($item) => $item['session']->startsOn($now)->gt($now))
            ?? $available->first();

        return [
            'ok' => true,
            'message' => 'Choose the session to check in for.',
            'occurrence' => $occurrence,
            'session' => $suggested['session'],
            'sessions' => $list,
        ];
    }

    /**
     * Records the check-in. With sessions, $sessionId picks the session
     * (any of today's not yet checked in); without it, the suggested one -
     * the session running now - is used, so older app versions keep working.
     *
     * @return array{ok: bool, message: string, attendance: ?ProgramAttendance, session: ?ProgramSession}
     */
    public function checkIn(ProgramRegistration $registration, ?int $recordedBy, ?int $sessionId = null): array
    {
        $check = $this->evaluate($registration);
        if (!$check['ok']) {
            return ['ok' => false, 'message' => $check['message'], 'attendance' => null, 'session' => null];
        }

        $session = $check['session'];

        if ($check['sessions'] && $sessionId) {
            $picked = collect($check['sessions'])->first(fn ($item) => $item['session']->id === $sessionId);

            if (!$picked) {
                return ['ok' => false, 'message' => 'That session is not part of this program.', 'attendance' => null, 'session' => null];
            }
            if ($picked['checked_in_at']) {
                return [
                    'ok' => false,
                    'message' => "Already checked in for the {$picked['session']->name} today at " . $picked['checked_in_at']->format('H:i') . '.',
                    'attendance' => null,
                    'session' => $picked['session'],
                ];
            }
            $session = $picked['session'];
        }

        $attendance = ProgramAttendance::create([
            'program_id' => $registration->program_id,
            'occurrence_id' => $check['occurrence']->id,
            'session_id' => optional($session)->id,
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
            'message' => $session ? "Checked in for the {$session->name}." : 'Checked in successfully.',
            'attendance' => $attendance,
            'session' => $session,
        ];
    }
}
