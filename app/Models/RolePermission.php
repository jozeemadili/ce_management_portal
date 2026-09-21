<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RolePermission
 * 
 * @property int $id
 * @property int $designation_id
 * @property int $permission_id
 * 
 * @property MemberDesignation $member_designation
 * @property Permission $permission
 *
 * @package App\Models
 */
class RolePermission extends Model
{
	protected $table = 'role_permissions';
	public $timestamps = false;

	protected $casts = [
		'designation_id' => 'int',
		'permission_id' => 'int'
	];

	protected $fillable = [
		'designation_id',
		'permission_id'
	];

	public function member_designation()
	{
		return $this->belongsTo(MemberDesignation::class, 'designation_id');
	}

	public function permission()
	{
		return $this->belongsTo(Permission::class);
	}
}
