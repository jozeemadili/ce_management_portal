<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Church
 * 
 * @property int $id
 * @property string $name
 * @property string $physical_location
 * @property string $status
 * @property int $designation_id
 * @property int|null $parent_church_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property ChurchDesignation $church_designation
 * @property Church|null $church
 * @property Collection|CellGroup[] $cell_groups
 * @property Collection|ChurchHierarchy[] $church_hierarchies
 * @property Collection|Church[] $churches
 * @property Collection|Department[] $departments
 * @property Collection|Member[] $members
 *
 * @package App\Models
 */
class Church extends Model
{
	protected $table = 'churches';

	protected $casts = [
		'designation_id' => 'int',
		'parent_church_id' => 'int'
	];

	protected $fillable = [
		'name',
		'physical_location',
		'designation_id',
		'parent_church_id',
		'status'
	];

	public function church_designation()
	{
		return $this->belongsTo(ChurchDesignation::class, 'designation_id');
	}

	public function church()
	{
		return $this->belongsTo(Church::class, 'parent_church_id');
	}

	public function cell_groups()
	{
		return $this->hasMany(CellGroup::class);
	}

	public function church_hierarchies()
	{
		return $this->hasMany(ChurchHierarchy::class, 'parent_church_id');
	}

	public function churches()
	{
		return $this->hasMany(Church::class, 'parent_church_id');
	}

	public function departments()
	{
		return $this->hasMany(Department::class);
	}

	public function members()
	{
		return $this->hasMany(Member::class)->where('member_type', 'member');
	}

	public function head_of_church_units()
	{
		return $this->hasMany(HeadOfChurchUnit::class, 'unit_id');
	}

	/**
	 * The most recent head-of-unit assignment for this church (the current pastor/leader).
	 */
	public function pledge_campaigns()
	{
		return $this->hasMany(PledgeCampaign::class);
	}

	public function current_head()
	{
		return $this->hasOne(HeadOfChurchUnit::class, 'unit_id')->latestOfMany('reg_date');
	}
}
