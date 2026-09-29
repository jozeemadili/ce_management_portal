<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProgramOccurrence
 *
 * A single calendar date on which a program happens. For a recurring
 * program (e.g. Sunday Service) this is one row per Sunday, created on
 * demand. For a special one-off program, exactly one row is created at
 * program-creation time, matching its start_date.
 *
 * @property int $id
 * @property int $program_id
 * @property Carbon $occurrence_date
 * @property string|null $start_time
 * @property string|null $end_time
 * @property string $status
 */
class ProgramOccurrence extends Model
{
    protected $table = 'program_occurrences';

    protected $casts = [
        'program_id' => 'int',
        'church_id' => 'int',
        'occurrence_date' => 'date',
        'closed_at' => 'datetime',
        'report_closed_at' => 'datetime',
        'report_summary' => 'array',
    ];

    protected $fillable = [
        'program_id', 'church_id', 'occurrence_date', 'start_time', 'end_time', 'status', 'closed_at',
        'report_closed_at', 'report_closed_by', 'report_summary',
    ];

    public function isReportClosed(): bool
    {
        return $this->report_closed_at !== null;
    }

    public function reportClosedBy()
    {
        return $this->belongsTo(User::class, 'report_closed_by');
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    /** Attendance has been closed (people not checked in marked absent). */
    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function attendances()
    {
        return $this->hasMany(ProgramAttendance::class, 'occurrence_id');
    }
}
