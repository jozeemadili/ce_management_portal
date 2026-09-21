<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProgramAttendance
 *
 * One unified table for both attendance modes: a member-list "Present" tick
 * during a recurring service, or a QR scan check-in at a special program.
 * The attendee is always a Member row - real congregation or a "new soul"
 * (member_type on the related Member row), never a separate model.
 *
 * @property int $id
 * @property int $program_id
 * @property int $occurrence_id
 * @property int|null $member_id
 * @property int|null $registration_id
 * @property string $attendance_status
 * @property string $check_in_method
 * @property Carbon|null $checked_in_at
 * @property int|null $recorded_by
 * @property string|null $notes
 */
class ProgramAttendance extends Model
{
    protected $table = 'program_attendances';

    protected $casts = [
        'program_id' => 'int',
        'occurrence_id' => 'int',
        'member_id' => 'int',
        'registration_id' => 'int',
        'recorded_by' => 'int',
        'checked_in_at' => 'datetime',
    ];

    protected $fillable = [
        'program_id', 'occurrence_id', 'member_id', 'registration_id',
        'attendance_status', 'check_in_method', 'checked_in_at', 'recorded_by', 'notes',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function occurrence()
    {
        return $this->belongsTo(ProgramOccurrence::class, 'occurrence_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function registration()
    {
        return $this->belongsTo(ProgramRegistration::class, 'registration_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function attendeeName(): string
    {
        if ($this->member) {
            return trim($this->member->first_name . ' ' . $this->member->last_name);
        }

        return 'Unknown';
    }

    public function isNewSoul(): bool
    {
        return $this->member && $this->member->member_type === 'new_soul';
    }
}
