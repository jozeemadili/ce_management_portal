<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A payment against a program registration - the same pattern as
 * PledgeContribution: partial payments add up until the amount due is met.
 * Recorded by staff: confirmed at once. Submitted by the attendee (or whoever
 * registered them) with a proof of payment: pending until staff confirm it.
 * Only confirmed payments count as paid.
 *
 * @property int $id
 * @property int $registration_id
 * @property float $amount
 * @property \Illuminate\Support\Carbon $payment_date
 * @property string|null $payment_method
 * @property string|null $payment_reference
 * @property int|null $recorded_by
 * @property string|null $notes
 * @property string $status pending|confirmed|rejected
 * @property string|null $proof_path
 * @property int|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property string|null $review_note
 */
class ProgramPayment extends Model
{
    protected $table = 'program_payments';

    protected $casts = [
        'registration_id' => 'int',
        'recorded_by' => 'int',
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'reviewed_by' => 'int',
        'reviewed_at' => 'datetime',
    ];

    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const REJECTED = 'rejected';

    protected $fillable = [
        'registration_id', 'amount', 'payment_date', 'payment_method',
        'payment_reference', 'recorded_by', 'notes',
        'status', 'proof_path', 'reviewed_by', 'reviewed_at', 'review_note',
    ];

    public function registration()
    {
        return $this->belongsTo(ProgramRegistration::class, 'registration_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::CONFIRMED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    /** "Awaiting confirmation" / "Confirmed" / "Rejected" */
    public function statusLabel(): string
    {
        return [
            self::PENDING => 'Awaiting confirmation',
            self::CONFIRMED => 'Confirmed',
            self::REJECTED => 'Rejected',
        ][$this->status] ?? ucfirst((string) $this->status);
    }
}
