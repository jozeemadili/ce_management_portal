<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CellGroupMember
 * 
 * @property int $id
 * @property int $cell_group_id
 * @property int $member_id
 * @property string|null $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property CellGroup $cell_group
 * @property Member $member
 *
 * @package App\Models
 */
class CellGroupMember extends Model
{
	protected $table = 'cell_group_members';

	protected $casts = [
		'cell_group_id' => 'int',
		'member_id' => 'int'
	];

	protected $fillable = [
		'cell_group_id',
		'member_id',
		'role'
	];

	public function cell_group()
	{
		return $this->belongsTo(CellGroup::class);
	}

	public function member()
	{
		return $this->belongsTo(Member::class);
	}
}
