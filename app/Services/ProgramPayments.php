<?php

namespace App\Services;

use App\Models\ProgramAuditLog;
use App\Models\ProgramPayment;
use App\Models\ProgramRegistration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Payments against paid program registrations, shared by the portal and the
 * mobile API:
 *  - submit(): the attendee (or whoever registered them) pays in full or in
 *    part and uploads a proof of payment - pending until staff confirm it;
 *  - record(): staff record a payment (proof optional) - confirmed at once;
 *  - confirm() / reject(): staff check a submitted proof.
 * Only confirmed payments count towards "Paid" and allow check-in.
 */
class ProgramPayments
{
    /** Where proofs are kept: the private "local" disk, never public. */
    public const PROOF_DIR = 'payment-proofs';

    public const PROOF_RULES = 'file|max:5120|mimes:jpg,jpeg,png,webp,heic,pdf';

    public function submit(ProgramRegistration $registration, array $data, UploadedFile $proof, int $userId): ProgramPayment
    {
        return $this->create($registration, $data, $proof, $userId, ProgramPayment::PENDING, 'registration.payment_submitted');
    }

    public function record(ProgramRegistration $registration, array $data, ?UploadedFile $proof, int $userId): ProgramPayment
    {
        return $this->create($registration, $data, $proof, $userId, ProgramPayment::CONFIRMED, 'registration.payment_recorded');
    }

    public function confirm(ProgramPayment $payment, int $reviewerId): void
    {
        $this->review($payment, ProgramPayment::CONFIRMED, $reviewerId, null, 'registration.payment_confirmed');
    }

    public function reject(ProgramPayment $payment, int $reviewerId, string $reason): void
    {
        $this->review($payment, ProgramPayment::REJECTED, $reviewerId, $reason, 'registration.payment_rejected');
    }

    /**
     * Checks a payment can be taken for this registration and the amount fits
     * what's still payable (balance minus payments awaiting confirmation).
     * Throws a ValidationException with a readable reason otherwise.
     */
    public function assertPayable(ProgramRegistration $registration, float $amount): void
    {
        $registration->loadMissing(['program', 'payments']);
        $currency = optional($registration->program)->currency;

        $fail = fn (string $message) => throw ValidationException::withMessages(['amount' => $message]);

        if ($registration->registration_status !== 'registered') {
            $fail('This registration has been cancelled and cannot receive payments.');
        }
        if ((float) $registration->amount_due <= 0) {
            $fail('Nothing to pay: this registration is free.');
        }
        if ($registration->balance() <= 0) {
            $fail('This registration is already fully paid.');
        }

        $payable = $registration->payableAmount();
        if ($payable <= 0) {
            $fail('The remaining balance is already covered by payments awaiting confirmation.');
        }
        if ($amount > $payable) {
            $fail("The amount can't be more than {$currency} " . number_format($payable, 2)
                . ($registration->pendingPaymentsTotal() > 0 ? ' (balance less payments awaiting confirmation).' : ' (the balance).'));
        }
    }

    private function create(ProgramRegistration $registration, array $data, ?UploadedFile $proof, int $userId, string $status, string $action): ProgramPayment
    {
        $this->assertPayable($registration, (float) $data['amount']);

        $payment = ProgramPayment::create([
            'registration_id' => $registration->id,
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'] ?? null,
            'payment_reference' => $data['payment_reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recorded_by' => $userId,
            'status' => $status,
            'proof_path' => $proof ? $proof->store(self::PROOF_DIR . '/' . $registration->id, 'local') : null,
            'reviewed_by' => $status === ProgramPayment::CONFIRMED ? $userId : null,
            'reviewed_at' => $status === ProgramPayment::CONFIRMED ? now() : null,
        ]);

        $this->refreshRegistration($registration);
        ProgramAuditLog::record($action, $registration, null, $payment->toArray());

        return $payment;
    }

    private function review(ProgramPayment $payment, string $status, int $reviewerId, ?string $note, string $action): void
    {
        if (!$payment->isPending()) {
            throw ValidationException::withMessages(['payment' => 'This payment has already been ' . strtolower($payment->statusLabel()) . '.']);
        }

        $registration = $payment->registration()->with(['program', 'payments'])->first();

        if ($status === ProgramPayment::CONFIRMED) {
            // Confirming mustn't take the total past what is owed.
            $room = max(0, (float) $registration->amount_due - $registration->totalPaid());
            if ((float) $payment->amount > $room) {
                throw ValidationException::withMessages(['payment' => 'Confirming this would pay more than the amount due (only '
                    . optional($registration->program)->currency . ' ' . number_format($room, 2) . ' is left). Reject it instead.']);
            }
        }

        $payment->update([
            'status' => $status,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_note' => $note,
        ]);

        $this->refreshRegistration($registration);
        ProgramAuditLog::record($action, $registration, null, $payment->fresh()->toArray());
    }

    private function refreshRegistration(ProgramRegistration $registration): void
    {
        $registration->unsetRelation('payments');
        $registration->recalculatePaymentStatus();
    }

    public function proofExists(ProgramPayment $payment): bool
    {
        return $payment->proof_path && Storage::disk('local')->exists($payment->proof_path);
    }
}
