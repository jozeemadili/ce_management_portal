<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One step in a new invitee's church-assignment history. */
class NewSoulAssignment extends Model
{
    protected $fillable = ['member_id', 'from_church_id', 'to_church_id', 'occurrence_id', 'assigned_by', 'note'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function fromChurch()
    {
        return $this->belongsTo(Church::class, 'from_church_id');
    }

    public function toChurch()
    {
        return $this->belongsTo(Church::class, 'to_church_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function occurrence()
    {
        return $this->belongsTo(ProgramOccurrence::class, 'occurrence_id');
    }
}
