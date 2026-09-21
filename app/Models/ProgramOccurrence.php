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
        'occurrence_date' => 'date',
    ];

    protected $fillable = [
        'program_id', 'occurrence_date', 'start_time', 'end_time', 'status',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function attendances()
    {
        return $this->hasMany(ProgramAttendance::class, 'occurrence_id');
    }
}
