<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A payment recorded against a program registration - the same pattern as
 * PledgeContribution: partial payments add up until the amount due is met.
 *
 * @property int $id
 * @property int $registration_id
 * @property float $amount
 * @property \Illuminate\Support\Carbon $payment_date
 * @property string|null $payment_method
 * @property string|null $payment_reference
 * @property int|null $recorded_by
 * @property string|null $notes
 */
class ProgramPayment extends Model
{
    protected $table = 'program_payments';

    protected $casts = [
        'registration_id' => 'int',
        'recorded_by' => 'int',
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected $fillable = [
        'registration_id', 'amount', 'payment_date', 'payment_method',
        'payment_reference', 'recorded_by', 'notes',
    ];

    public function registration()
    {
        return $this->belongsTo(ProgramRegistration::class, 'registration_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
