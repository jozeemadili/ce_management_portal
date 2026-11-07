<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A Foundation School class a person has completed (class_no 1..TOTAL).
 *
 * @property int $member_id
 * @property int $class_no
 * @property \Illuminate\Support\Carbon|null $completed_on
 */
class MemberFoundationClass extends Model
{
    /** Number of Foundation School classes; completing all makes a new soul a member. */
    public const TOTAL = 7;

    protected $fillable = ['member_id', 'class_no', 'completed_on', 'recorded_by'];

    protected $casts = ['class_no' => 'int', 'completed_on' => 'date'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
