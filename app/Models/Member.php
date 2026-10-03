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
		'title_id',
		'first_name',
		'last_name',
		'phone',
		'email',
		'kingschat_username',
		'location',
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
		'invited_by_member_id',
		'first_visit_program_id',
		'first_visit_date',
		'recorded_by',
		'is_training',

	];

	public function church()
	{
		return $this->belongsTo(Church::class);
	}

	/** Brother, Sister, Deacon... */
	public function title()
	{
		return $this->belongsTo(MemberTitle::class, 'title_id');
	}

	/** "Brother John Mushi" (title when set). */
	public function titledName(): string
	{
		return trim(optional($this->title)->name . ' ' . $this->first_name . ' ' . $this->last_name);
	}

	/** Church assignment history (newest first) - see NewSoulAssignment. */
	public function assignments()
	{
		return $this->hasMany(NewSoulAssignment::class)->latest('id');
	}

	/** The member who brought this new soul to church. */
	public function invitedByMember()
	{
		return $this->belongsTo(Member::class, 'invited_by_member_id');
	}

	/**
	 * Code in the member's personal check-in QR, created on first use.
	 * Random (not the member id) so a QR can't be guessed or forged.
	 */
	public function checkinToken(): string
	{
		if (!$this->checkin_token) {
			do {
				$token = \Illuminate\Support\Str::random(32);
			} while (static::where('checkin_token', $token)->exists());

			$this->forceFill(['checkin_token' => $token])->save();
		}

		return $this->checkin_token;
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
		// Training new souls (from a training service) are practice data.
		return $query->where('member_type', 'new_soul')->where('is_training', false);
	}

	/**
	 * KingsChat usernames are stored without a leading "@"; blank -> null.
	 */
	public static function normaliseKingschat($value): ?string
	{
		$value = ltrim(trim((string) $value), '@');

		return $value === '' ? null : mb_substr($value, 0, 100);
	}

	/**
	 * The ways one phone number can be stored in members.phone
	 * (0712345678, 255712345678, +255712345678, 712345678).
	 */
	public static function phoneVariants($phone): array
	{
		$mobile = \App\Services\AccountLogin::normaliseMobile($phone);

		return $mobile ? ['0' . $mobile, '255' . $mobile, '+255' . $mobile, (string) $mobile] : [];
	}

	/** Same phone number, whatever format it was stored in. */
	public function scopeWithPhone($query, $phone)
	{
		return $query->whereIn('phone', self::phoneVariants($phone) ?: ['__none__']);
	}

	/** "Name (Church)" for messages. */
	public function describe(): string
	{
		$name = trim($this->first_name . ' ' . $this->last_name);
		$church = optional($this->church)->name;

		return $church ? "{$name} ({$church})" : $name;
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
			// Fill in details we didn't have yet; never overwrite what is there.
			$missing = collect(['kingschat_username', 'email', 'location'])
				->filter(fn ($field) => !empty($attributes[$field]) && empty($member->{$field}))
				->mapWithKeys(fn ($field) => [$field => $attributes[$field]])
				->all();
			if ($missing) {
				$member->update($missing);
			}

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
