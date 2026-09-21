<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HeadOfChurchUnit
 *
 * Tracks who has been assigned as the head (pastor/leader) of a church
 * unit over time. Every assignment/reassignment inserts a new row, so
 * the current head of a church is the row with the latest reg_date.
 *
 * @property int $id
 * @property int $unit_id
 * @property int $head_of_unit
 * @property Carbon $reg_date
 * @property int|null $reg_by
 *
 * @property Church $church
 * @property Member $member
 * @property User|null $registered_by
 *
 * @package App\Models
 */
class HeadOfChurchUnit extends Model
{
    protected $table = 'head_of_church_units';
    public $timestamps = false;

    protected $casts = [
        'unit_id' => 'int',
        'head_of_unit' => 'int',
        'reg_date' => 'datetime',
        'reg_by' => 'int',
    ];

    protected $fillable = [
        'unit_id',
        'head_of_unit',
        'reg_date',
        'reg_by',
    ];

    public function church()
    {
        return $this->belongsTo(Church::class, 'unit_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'head_of_unit');
    }

    public function registered_by()
    {
        return $this->belongsTo(User::class, 'reg_by');
    }
}
