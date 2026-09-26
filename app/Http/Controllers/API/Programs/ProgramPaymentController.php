<?php

namespace App\Http\Controllers\API\Programs;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\PledgePaymentMethod;
use App\Models\ProgramPayment;
use App\Models\ProgramRegistration;
use App\Services\ProgramPayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Payments against paid program registrations (see App\Services\ProgramPayments):
 * staff record payments (confirmed at once), attendees submit payments with
 * a proof from their registration page (pending until confirmed), and staff
 * confirm or reject submitted proofs.
 */
class ProgramPaymentController extends Controller
{
    use AuthorizesPrograms;

    private function paymentRules(bool $proofRequired): array
    {
        return [
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'proof' => ($proofRequired ? 'required|' : 'nullable|') . ProgramPayments::PROOF_RULES,
        ];
    }

    private function messages(): array
    {
        return [
            'proof.required' => 'Attach a proof of payment (photo, screenshot or PDF of the receipt).',
            'proof.mimes' => 'The proof must be a photo (JPG, PNG, WEBP, HEIC) or a PDF.',
            'proof.max' => 'The proof file must be 5 MB or smaller.',
        ];
    }

    /** Whoever the registration belongs to, or whoever registered them. */
    private function isOwnerOrRegistrar(ProgramRegistration $registration): bool
    {
        $member = Auth::user()->member;

        return $registration->registered_by === Auth::id()
            || ($member && $registration->member_id === $member->id);
    }

    /** Staff: record a payment (proof optional) - counts immediately. */
    public function store(Request $request, ProgramRegistration $registration, ProgramPayments $payments)
    {
        $this->authorizeProgram('PROGRAMS_MANAGE_REGISTRATIONS');

        $data = $request->validate($this->paymentRules(false), $this->messages());
        $payment = $payments->record($registration, $data, $request->file('proof'), Auth::id());

        $registration->refresh();

        return back()->with('success', $this->summary($registration, $payment, 'recorded'));
    }

    /**
     * Attendee (or whoever registered them): submit a full or partial
     * payment with its proof, from the registration details page.
     */
    public function submit(Request $request, ProgramRegistration $registration, ProgramPayments $payments)
    {
        abort_unless($this->isOwnerOrRegistrar($registration), 403, 'You can only pay for registrations you made or your own.');

        $data = $request->validate($this->paymentRules(true), $this->messages());
        $payment = $payments->submit($registration, $data, $request->file('proof'), Auth::id());

        return back()->with('success', 'Payment of ' . $registration->program->currency . ' ' . number_format($payment->amount, 2)
            . ' submitted. It will count once the church confirms your proof of payment.');
    }

    public function confirm(ProgramPayment $payment, ProgramPayments $payments)
    {
        $this->authorizeProgram('PROGRAMS_MANAGE_REGISTRATIONS');

        $payments->confirm($payment, Auth::id());
        $registration = $payment->registration()->with('program')->first();

        return back()->with('success', $this->summary($registration, $payment, 'confirmed'));
    }

    public function reject(Request $request, ProgramPayment $payment, ProgramPayments $payments)
    {
        $this->authorizeProgram('PROGRAMS_MANAGE_REGISTRATIONS');

        $data = $request->validate(['review_note' => 'required|string|max:255'], [
            'review_note.required' => 'Give a reason, so the person knows what to fix.',
        ]);
        $payments->reject($payment, Auth::id(), $data['review_note']);

        return back()->with('success', 'Payment rejected. The person can submit a new payment with a correct proof.');
    }

    /**
     * The uploaded proof, shown in the browser from private storage. Open to
     * the payer and to staff - like the rest of this module, staff access is
     * any signed-in user while per-code permissions are relaxed.
     */
    public function proof(ProgramPayment $payment, ProgramPayments $payments)
    {
        $this->authorizeProgram('PROGRAMS_MANAGE_REGISTRATIONS');
        abort_unless($payments->proofExists($payment), 404, 'No proof of payment was uploaded.');

        return Storage::disk('local')->response($payment->proof_path);
    }

    /**
     * "Payments to Confirm": proofs submitted by attendees, oldest first,
     * for programs visible to the current user (same hierarchy scoping as
     * the rest of the module).
     */
    public function index(Request $request)
    {
        $this->authorizeProgram('PROGRAMS_MANAGE_REGISTRATIONS');

        $status = in_array($request->status, ['pending', 'confirmed', 'rejected'], true) ? $request->status : 'pending';
        $churchIds = $this->scopedChurchIds();

        $payments = ProgramPayment::with(['registration.program', 'registration.member.church', 'registration.pricedDesignation', 'recorder', 'reviewer'])
            ->where('status', $status)
            ->when(!is_null($churchIds), fn ($q) => $q->whereHas('registration.program', fn ($p) => $p->where(function ($x) use ($churchIds) {
                $x->where('scope', 'global')->orWhereIn('church_id', $churchIds);
            })))
            ->orderBy($status === 'pending' ? 'created_at' : 'reviewed_at', $status === 'pending' ? 'asc' : 'desc')
            ->paginate(20)
            ->withQueryString();

        $pendingCount = ProgramPayment::pending()->count();

        return view('portal.programs.payments.index', compact('payments', 'status', 'pendingCount'));
    }

    private function scopedChurchIds()
    {
        $member = Auth::user()->member;
        if (!$member) {
            return Auth::user()->role === 'ADMIN' ? null : collect([0]);
        }
        $userChurch = Church::find($member->church_id);
        if ($userChurch && is_null($userChurch->parent_church_id)) {
            return null;
        }

        return Church::where('id', $userChurch->id ?? 0)->orWhere('parent_church_id', $userChurch->id ?? 0)->pluck('id');
    }

    private function summary(ProgramRegistration $registration, ProgramPayment $payment, string $verb): string
    {
        $currency = optional($registration->program)->currency;
        $status = $registration->payment_status === 'paid'
            ? 'now fully paid'
            : 'balance ' . $currency . ' ' . number_format($registration->balance(), 2);

        return "Payment of {$currency} " . number_format($payment->amount, 2)
            . " {$verb} for {$registration->registration_reference} ({$status}).";
    }

    /** Payment methods for the payment forms (the pledge payment methods list). */
    public static function methods()
    {
        return PledgePaymentMethod::where('is_active', true)->orderBy('name')->get();
    }
}
