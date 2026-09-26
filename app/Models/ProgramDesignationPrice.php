<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * What a paid program costs for one member designation (group).
 *
 * @property int $id
 * @property int $program_id
 * @property int $designation_id
 * @property float $amount
 */
class ProgramDesignationPrice extends Model
{
    protected $table = 'program_designation_prices';

    protected $casts = [
        'program_id' => 'int',
        'designation_id' => 'int',
        'amount' => 'decimal:2',
    ];

    protected $fillable = [
        'program_id', 'designation_id', 'amount',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function designation()
    {
        return $this->belongsTo(MemberDesignation::class, 'designation_id');
    }
}
