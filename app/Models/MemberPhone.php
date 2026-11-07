<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An extra phone number of a person (normalised 0XXXXXXXXX). */
class MemberPhone extends Model
{
    protected $fillable = ['member_id', 'phone', 'label', 'added_by'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
