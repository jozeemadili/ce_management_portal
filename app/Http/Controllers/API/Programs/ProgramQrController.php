<?php

namespace App\Http\Controllers\API\Programs;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\PledgePaymentMethod;
use App\Models\ProgramRegistration;
use App\Services\ProgramCheckIn;
use Illuminate\Support\Facades\Auth;

class ProgramQrController extends Controller
{
    use AuthorizesPrograms;

    /**
     * Landing page for the QR code printed on a registration's PDF - shows
     * the program + member at a glance, the session that is open now, and a
     * "Confirm Check-In" button (or the reason check-in isn't allowed).
     * Rules live in App\Services\ProgramCheckIn, shared with the mobile API.
     */
    public function show(ProgramRegistration $registration, ProgramCheckIn $checkIn)
    {
        $this->authorizeProgram('ATTENDANCE_SCAN_QR');

        $registration->load(['program.sessions', 'member.church', 'attendance.session', 'payments', 'pricedDesignation']);

        $check = $checkIn->evaluate($registration);
        $ok = $check['ok'];
        $message = $check['message'];
        $session = $check['session'];
        $paymentMethods = PledgePaymentMethod::where('is_active', true)->orderBy('name')->get();

        return view('portal.programs.scan.show', compact('registration', 'ok', 'message', 'session', 'paymentMethods'));
    }

    public function checkIn(ProgramRegistration $registration, ProgramCheckIn $checkIn)
    {
        $this->authorizeProgram('ATTENDANCE_SCAN_QR');

        $registration->load('program');
        $result = $checkIn->checkIn($registration, Auth::id());

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
}
