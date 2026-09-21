<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ChurchHierarchy
 * 
 * @property int $id
 * @property int $church_id
 * @property int $parent_church_id
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 * @property int|null $changed_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Church $church
 *
 * @package App\Models
 */
class ChurchHierarchy extends Model
{
	protected $table = 'church_hierarchy';

	protected $casts = [
		'church_id' => 'int',
		'parent_church_id' => 'int',
		'changed_by' => 'int'
	];

	protected $dates = [
		'start_date',
		'end_date'
	];

	protected $fillable = [
		'church_id',
		'parent_church_id',
		'start_date',
		'end_date',
		'changed_by'
	];

	public function church()
	{
		return $this->belongsTo(Church::class, 'parent_church_id');
	}
}
