<?php

namespace App\Http\Controllers\API\Programs;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\ProgramAuditLog;
use App\Models\ProgramPayment;
use App\Models\ProgramRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Records payments against a paid program registration - same pattern as
 * pledge contributions: partial payments add up until the amount due is
 * covered, then the registration is Paid and can be checked in.
 */
class ProgramPaymentController extends Controller
{
    use AuthorizesPrograms;

    public function store(Request $request, ProgramRegistration $registration)
    {
        $this->authorizeProgram('PROGRAMS_MANAGE_REGISTRATIONS');

        $registration->load(['program', 'payments']);
        $currency = optional($registration->program)->currency;

        if ($registration->registration_status !== 'registered') {
            return back()->withErrors('This registration has been cancelled and cannot receive payments.');
        }
        if ((float) $registration->amount_due <= 0) {
            return back()->withErrors('Nothing to pay: this registration is free.');
        }

        $balance = $registration->balance();
        if ($balance <= 0) {
            return back()->withErrors('This registration is already fully paid.');
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $balance,
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ], [
            'amount.max' => "The amount can't be more than the balance of {$currency} " . number_format($balance, 2) . '.',
        ]);

        $payment = ProgramPayment::create($data + [
            'registration_id' => $registration->id,
            'recorded_by' => Auth::id(),
        ]);

        $registration->unsetRelation('payments');
        $registration->recalculatePaymentStatus();

        ProgramAuditLog::record('registration.payment_recorded', $registration, null, $payment->toArray());

        $status = $registration->payment_status === 'paid'
            ? 'now fully paid'
            : 'balance ' . $currency . ' ' . number_format($registration->balance(), 2);

        return back()->with('success', "Payment of {$currency} " . number_format($payment->amount, 2)
            . " recorded for {$registration->registration_reference} ({$status}).");
    }
}
