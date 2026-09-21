<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CellGroup
 * 
 * @property int $id
 * @property int $church_id
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Church $church
 * @property Collection|Member[] $members
 *
 * @package App\Models
 */
class CellGroup extends Model
{
	protected $table = 'cell_groups';

	protected $casts = [
		'church_id' => 'int'
	];

	protected $fillable = [
		'church_id',
		'name',
		'description'
	];

	public function church()
	{
		return $this->belongsTo(Church::class);
	}

	public function members()
	{
		return $this->belongsToMany(Member::class, 'cell_group_members')
					->where('members.member_type', 'member')
					->withPivot('id', 'role')
					->withTimestamps();
	}
}
