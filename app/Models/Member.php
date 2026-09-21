<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Member
 * 
 * @property int $id
 * @property int|null $user_id
 * @property int $church_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property string|null $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $foundation_clases_date
 * @property Carbon|null $baptism_date
 * @property Carbon|null $marriage_dates
 * @property string|null $foundation_clases
 * @property string|null $baptism_status
 * @property string|null $marriage_status

 * @property Church $church
 * @property Collection|CellGroup[] $cell_groups
 * @property Collection|Department[] $departments
 * @property Collection|MemberRole[] $member_roles
 *
 * @package App\Models
 */
class Member extends Model
{
	protected $table = 'members';

	protected $casts = [
		'user_id' => 'int',
		'church_id' => 'int',
		'foundation_clases_date' => 'date',
		'baptism_date' => 'date',
		'marriage_dates' => 'date',
		'date_of_birth' => 'date',
		'first_visit_date' => 'date',
		'first_visit_program_id' => 'int',
		'recorded_by' => 'int',
	];


	protected $fillable = [
		'user_id',
		'church_id',
		'first_name',
		'last_name',
		'phone',
		'email',
		'foundation_clases',
		'foundation_clases_date',
		'baptism_status',
		'baptism_date',
		'marriage_status',
		'marriage_dates',
		'member_type',
		'follow_up_status',
		'gender',
		'date_of_birth',
		'notes',
		'invited_by',
		'first_visit_program_id',
		'first_visit_date',
		'recorded_by',

	];

	public function church()
	{
		return $this->belongsTo(Church::class);
	}

	public function cell_groups()
	{
		return $this->belongsToMany(CellGroup::class, 'cell_group_members')
					->withPivot('id', 'role')
					->withTimestamps();
	}

	public function departments()
	{
		return $this->belongsToMany(Department::class, 'department_members')
					->withPivot('id', 'role')
					->withTimestamps();
	}

	public function member_roles()
	{
		return $this->hasMany(MemberRole::class);
	}

	public function pledges()
	{
		return $this->hasMany(Pledge::class);
	}

	public function firstVisitProgram()
	{
		return $this->belongsTo(Program::class, 'first_visit_program_id');
	}

	public function scopeNewSouls($query)
	{
		return $query->where('member_type', 'new_soul');
	}

	public function isNewSoul(): bool
	{
		return $this->member_type === 'new_soul';
	}

	/**
	 * Find an existing member by phone (or first+last name within the same
	 * church) so a returning visitor/registrant is recognized instead of
	 * creating a duplicate person - or create a new "new soul" tagged
	 * member if nobody matches. Used by both the attendance-capture
	 * "record a first-time visitor" mini-form and the program registration
	 * desk.
	 */
	public static function findOrCreateNewSoul(array $attributes, int $churchId, ?int $recordedBy = null): self
	{
		$query = static::query();

		if (!empty($attributes['phone'])) {
			$query->where('phone', $attributes['phone']);
		} else {
			$query->where('first_name', $attributes['first_name'])
				  ->where('last_name', $attributes['last_name'] ?? null)
				  ->where('church_id', $churchId);
		}

		$member = $query->first();

		if ($member) {
			return $member;
		}

		return static::create(array_merge($attributes, [
			'church_id' => $churchId,
			'member_type' => 'new_soul',
			'follow_up_status' => 'new',
			'recorded_by' => $recordedBy,
		]));
	}
}
