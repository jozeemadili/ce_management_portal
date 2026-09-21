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
        'registered_at' => 'datetime',
    ];

    protected $fillable = [
        'program_id', 'member_id', 'registered_by', 'registration_reference', 'registration_status',
        'payment_status', 'amount_paid', 'registered_at',
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
        return $this->hasOne(ProgramAttendance::class, 'registration_id');
    }

    /**
     * Register a member for a program, stamping a REG-000123 reference and
     * the correct starting payment status (free programs need no payment
     * step at all).
     */
    public static function createFor(Member $member, Program $program, ?int $registeredBy = null): self
    {
        $registration = static::create([
            'program_id' => $program->id,
            'member_id' => $member->id,
            'registered_by' => $registeredBy,
            'registration_status' => 'registered',
            'payment_status' => $program->isFree() ? 'free' : 'pending',
            'registered_at' => now(),
        ]);

        $registration->registration_reference = 'REG-' . str_pad($registration->id, 6, '0', STR_PAD_LEFT);
        $registration->save();

        return $registration;
    }
}
