<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Company;
use App\Models\RolePermission;
use App\Models\Permission;



/**
 * Class User
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

	protected $table = 'users';

	protected $casts = [
		'mobile' => 'int',
		'remember_token' => 'bool',
		'is_first_time_pin' => 'bool',
		'company_id' => 'int',
		'created_by' => 'int',
		'authorized_by' => 'int',
		'designation_id' => 'int'
		
	];

	protected $dates = [
		'email_verified_at',
		'dob',
		'authorized_at'
	];

	protected $hidden = [
		'remember_token',
		'password'
	];

	protected $fillable = [
		'emp_id',
		'branch_id',
		'title',
		'first_name',
		'middle_name',
		'last_name',
		'type',
		'mobile',
		'id_type',
		'id_number',
		'email',
		'email_verified_at',
		'remember_token',
		'dob',
		'password',
		'is_first_time_pin',
		'role',
		'company_id',
		'status',
		'created_by',
		'authorized_by',
		'authorized_at',
		'designation_id'
	];
	

public function company()
{
    return $this->belongsTo(Company::class, 'company_id');
}





public function hasPermission(string $permissionCode): bool
{
    if (!$this->designation_id) {
        return false;
    }

    return RolePermission::where('designation_id', $this->designation_id)
        ->whereHas('permission', function ($q) use ($permissionCode) {
            $q->where('code', $permissionCode);
        })
        ->exists();
}
public function member()
{
    return $this->hasOne(\App\Models\Member::class);
}



}
