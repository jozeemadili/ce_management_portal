<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DepartmentMember
 * 
 * @property int $id
 * @property int $department_id
 * @property int $member_id
 * @property string|null $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Department $department
 * @property Member $member
 *
 * @package App\Models
 */
class DepartmentMember extends Model
{
	protected $table = 'department_members';

	protected $casts = [
		'department_id' => 'int',
		'member_id' => 'int'
	];

	protected $fillable = [
		'department_id',
		'member_id',
		'role'
	];

	public function department()
	{
		return $this->belongsTo(Department::class);
	}

	public function member()
	{
		return $this->belongsTo(Member::class);
	}
}
