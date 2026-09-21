<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ChurchDesignation
 * 
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Church[] $churches
 *
 * @package App\Models
 */
class ChurchDesignation extends Model
{
	protected $table = 'church_designations';

	protected $fillable = [
		'name'
	];

	public function churches()
	{
		return $this->hasMany(Church::class, 'designation_id');
	}
}
