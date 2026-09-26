<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProgramRegistration
 *
 * @property int $id
 * @property int $program_id
 * @property int $member_id
 * @property string|null $registration_reference
 * @property string $registration_status
 * @property string $payment_status
 * @property float|null $amount_paid
 * @property Carbon|null $registered_at
 */
class ProgramRegistration extends Model
{
    protected $table = 'program_registrations';

    protected $casts = [
        'program_id' => 'int',
        'member_id' => 'int',
        'registered_by' => 'int',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'priced_designation_id' => 'int',
        'registered_at' => 'datetime',
    ];

    protected $fillable = [
        'program_id', 'member_id', 'registered_by', 'registration_reference', 'registration_status',
        'payment_status', 'amount_paid', 'amount_due', 'priced_designation_id', 'registered_at',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function attendance()
    {
        return $this->hasOne(ProgramAttendance::class, 'registration_id')->latestOfMany('checked_in_at');
    }

    /** Every check-in (one per session per day). */
    public function attendances()
    {
        return $this->hasMany(ProgramAttendance::class, 'registration_id');
    }

    public function payments()
    {
        return $this->hasMany(ProgramPayment::class, 'registration_id');
    }

    /** The designation whose price was charged (null = free / no designation). */
    public function pricedDesignation()
    {
        return $this->belongsTo(MemberDesignation::class, 'priced_designation_id');
    }

    public function totalPaid(): float
    {
        return (float) ($this->relationLoaded('payments') ? $this->payments->sum('amount') : $this->payments()->sum('amount'));
    }

    public function balance(): float
    {
        return max(0, (float) $this->amount_due - $this->totalPaid());
    }

    /** Nothing left to pay: free, or fully paid. */
    public function isSettled(): bool
    {
        return in_array($this->payment_status, ['free', 'paid'], true);
    }

    /**
     * What this person owes: "FREE", or "TZS 40,000 (Coordinator)". Older
     * paid registrations made before group pricing fall back to the
     * program's single fee.
     */
    public function amountDueLabel(): string
    {
        $program = $this->program;
        $currency = optional($program)->currency;

        if ($this->amount_due === null) {
            return !$program || $program->isFree() ? 'FREE' : $currency . ' ' . number_format($program->registration_fee);
        }
        if ((float) $this->amount_due <= 0) {
            return 'FREE';
        }

        $group = $this->pricedDesignation ? ' (' . ucwords($this->pricedDesignation->name) . ')' : '';

        return $currency . ' ' . number_format($this->amount_due) . $group;
    }

    /** Free / Paid / Partially paid / Pending (for display). */
    public function paymentLabel(): string
    {
        if ($this->payment_status === 'pending' && $this->totalPaid() > 0) {
            return 'Partially paid';
        }

        return ucfirst($this->payment_status);
    }

    /**
     * After a payment is recorded: fully paid once payments cover the amount
     * due, otherwise still pending (partially paid). Keeps amount_paid in
     * step for older screens that read it.
     */
    public function recalculatePaymentStatus(): void
    {
        $paid = $this->totalPaid();
        $this->amount_paid = $paid;

        if ((float) $this->amount_due <= 0) {
            $this->payment_status = 'free';
        } else {
            $this->payment_status = $paid >= (float) $this->amount_due ? 'paid' : 'pending';
        }

        $this->save();
    }

    /**
     * "First-time visitor" (a new soul) or "Member" (the regular
     * congregation) - for reports.
     */
    public function attendeeTypeLabel(): string
    {
        return optional($this->member)->member_type === 'new_soul' ? 'First-time visitor' : 'Member';
    }

    /**
     * Who brought this person in, for reports: the user who registered them
     * ("Self" when they registered themselves), else the free-text Invited
     * By recorded on the person (e.g. from the attendance visitor form).
     */
    public function invitedByLabel(): string
    {
        $registrar = $this->registeredBy;

        if ($registrar) {
            if ($this->member && $this->member->user_id === $registrar->id) {
                return 'Self';
            }

            return trim($registrar->first_name . ' ' . $registrar->last_name) ?: ($registrar->email ?? '—');
        }

        return optional($this->member)->invited_by ?: '—';
    }

    /**
     * Register a member for a program, stamping a REG-000123 reference and
     * the correct starting payment status (free programs need no payment
     * step at all).
     */
    public static function createFor(Member $member, Program $program, ?int $registeredBy = null): self
    {
        // Price fixed now, from the attendee's most senior designation - a
        // later price change doesn't alter what this person owes.
        $price = $program->priceFor($member);

        $registration = static::create([
            'program_id' => $program->id,
            'member_id' => $member->id,
            'registered_by' => $registeredBy,
            'registration_status' => 'registered',
            'payment_status' => $price['amount'] > 0 ? 'pending' : 'free',
            'amount_due' => $price['amount'],
            'priced_designation_id' => optional($price['designation'])->id,
            'registered_at' => now(),
        ]);

        $registration->registration_reference = 'REG-' . str_pad($registration->id, 6, '0', STR_PAD_LEFT);
        $registration->save();

        return $registration;
    }
}
