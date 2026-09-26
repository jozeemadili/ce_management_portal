<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A named time slot of a program (e.g. Morning Session 09:00-12:00) that
 * repeats on every day the program runs. QR check-in is per session.
 *
 * @property int $id
 * @property int $program_id
 * @property string $name
 * @property string $start_time
 * @property string $end_time
 * @property int $sort_order
 */
class ProgramSession extends Model
{
    /** QR check-in opens this many minutes before a session starts. */
    public const CHECK_IN_OPENS_BEFORE_MINUTES = 60;

    protected $table = 'program_sessions';

    protected $casts = [
        'program_id' => 'int',
        'sort_order' => 'int',
    ];

    protected $fillable = [
        'program_id', 'name', 'start_time', 'end_time', 'sort_order',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function attendances()
    {
        return $this->hasMany(ProgramAttendance::class, 'session_id');
    }

    /** "09:00 – 12:00" */
    public function timeRange(): string
    {
        return substr($this->start_time, 0, 5) . ' – ' . substr($this->end_time, 0, 5);
    }

    /** This session's start/end on a given day. */
    public function startsOn(Carbon $day): Carbon
    {
        return $day->copy()->setTimeFromTimeString($this->start_time);
    }

    public function endsOn(Carbon $day): Carbon
    {
        return $day->copy()->setTimeFromTimeString($this->end_time);
    }

    /** Whether QR check-in is open for this session at $now. */
    public function isCheckInOpen(Carbon $now): bool
    {
        $opens = $this->startsOn($now)->subMinutes(self::CHECK_IN_OPENS_BEFORE_MINUTES);

        return $now->between($opens, $this->endsOn($now));
    }
}
