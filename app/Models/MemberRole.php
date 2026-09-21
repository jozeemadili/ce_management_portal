<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MemberRole
 * 
 * @property int $id
 * @property int $member_id
 * @property int $designation_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property MemberDesignation $member_designation
 * @property Member $member
 *
 * @package App\Models
 */
class MemberRole extends Model
{
	protected $table = 'member_roles';

	protected $casts = [
		'member_id' => 'int',
		'designation_id' => 'int'
	];

	protected $fillable = [
		'member_id',
		'designation_id'
	];

	public function member_designation()
	{
		return $this->belongsTo(MemberDesignation::class, 'designation_id');
	}

	public function member()
	{
		return $this->belongsTo(Member::class);
	}
}
