<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ProgramRegistration;
use App\Models\ProgramSession;
use App\Services\ProgramCheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramAttendanceController extends Controller
{
    private function sessionPayload(?ProgramSession $session): ?array
    {
        return $session ? [
            'id' => $session->id,
            'name' => $session->name,
            'start_time' => substr($session->start_time, 0, 5),
            'end_time' => substr($session->end_time, 0, 5),
        ] : null;
    }

    private function scanPayload(ProgramRegistration $registration, bool $ok, string $message, ?ProgramSession $session, array $sessionOptions = []): array
    {
        $program = $registration->program;
        $member = $registration->member;
        $lastCheckIn = $registration->attendance;

        return [
            'ok' => $ok,
            'message' => $message,
            // Suggested session (running now / next) - preselected in the app.
            'session' => $this->sessionPayload($session),
            // Today's sessions to choose from, with their check-in state.
            'session_options' => collect($sessionOptions)->map(fn ($item) => $this->sessionPayload($item['session']) + [
                'checked_in_at' => optional($item['checked_in_at'])->format('H:i'),
                'open_now' => $item['open_now'],
            ])->values(),
            'registration' => [
                'id' => $registration->id,
                'reference' => $registration->registration_reference,
                'registration_status' => $registration->registration_status,
                'payment_status' => $registration->payment_status,
                'payment_label' => $registration->paymentLabel(),
                'amount_due' => (float) $registration->amount_due,
                'amount_paid' => $registration->totalPaid(),
                'balance' => $registration->balance(),
                'price_group' => optional($registration->pricedDesignation)->name,
                'registered_at' => optional($registration->registered_at)->toDateTimeString(),
            ],
            'program' => $program ? [
                'id' => $program->id,
                'name' => $program->name,
                'location' => $program->location,
                'start_date' => optional($program->start_date)->toDateString(),
                'currency' => $program->currency,
                'sessions' => $program->sessions->map(fn ($s) => $this->sessionPayload($s))->values(),
            ] : null,
            'member' => $member ? [
                'id' => $member->id,
                'name' => trim($member->first_name . ' ' . $member->last_name),
                'church' => optional($member->church)->name,
            ] : null,
            'checked_in_at' => $lastCheckIn ? optional($lastCheckIn->checked_in_at)->toDateTimeString() : null,
            'checked_in_session' => $lastCheckIn ? optional($lastCheckIn->session)->name : null,
        ];
    }

    private function loadForScan(ProgramRegistration $registration): void
    {
        $registration->load(['program.sessions', 'member.church', 'attendance.session', 'payments', 'pricedDesignation']);
    }

    /**
     * Hit right after the app's camera scans a registration's QR code (the
     * printed/PDF QR encodes the web scan URL - the app extracts the
     * trailing numeric id from it and calls this with that id). Returns
     * whether check-in is allowed right now, for which session, and why not
     * if it's blocked - never a validation exception. Rules live in
     * App\Services\ProgramCheckIn, shared with the web scan page.
     */
    public function scan(Request $request, ProgramRegistration $registration, ProgramCheckIn $checkIn)
    {
        $this->loadForScan($registration);

        $check = $checkIn->evaluate($registration);

        return response()->json($this->scanPayload($registration, $check['ok'], $check['message'], $check['session'], $check['sessions']));
    }

    public function checkIn(Request $request, ProgramRegistration $registration, ProgramCheckIn $checkIn)
    {
        $this->loadForScan($registration);

        // session_id: the session picked in the app (optional - defaults to
        // the one running now, which is what older app versions rely on).
        $request->validate(['session_id' => 'nullable|integer']);
        $result = $checkIn->checkIn($registration, Auth::id(), $request->integer('session_id') ?: null);

        $registration->unsetRelation('attendance');
        $registration->load('attendance.session');
        $after = $checkIn->evaluate($registration);
        $payload = $this->scanPayload($registration, $result['ok'], $result['message'], $result['session'], $after['sessions']);

        return response()->json($payload, $result['ok'] ? 201 : 422);
    }
}
