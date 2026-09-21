<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Ward
 * 
 * @property int $id
 * @property string $name
 * @property int $district_id
 *
 * @package App\Models
 */
class Ward extends Model
{
	protected $table = 'wards';
	public $timestamps = false;

	protected $casts = [
		'district_id' => 'int'
	];

	protected $fillable = [
		'name',
		'district_id'
	];
}
