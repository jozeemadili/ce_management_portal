<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A church's own start/end time for a church service (recurring program).
 * Without a row the church uses the service's default time.
 */
class ServiceChurchTime extends Model
{
    protected $fillable = ['program_id', 'church_id', 'start_time', 'end_time', 'updated_by'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}
