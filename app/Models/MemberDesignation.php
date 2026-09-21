<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MemberDesignation
 * 
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|MemberRole[] $member_roles
 * @property Collection|RolePermission[] $role_permissions
 *
 * @package App\Models
 */
class MemberDesignation extends Model
{
	protected $table = 'member_designations';

	protected $fillable = [
		'name'
	];

	public function member_roles()
	{
		return $this->hasMany(MemberRole::class, 'designation_id');
	}

	public function role_permissions()
	{
		return $this->hasMany(RolePermission::class, 'designation_id');
	}
}
