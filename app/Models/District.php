<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class District
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $region_id
 *
 * @package App\Models
 */
class District extends Model
{
	protected $table = 'districts';
	public $timestamps = false;

	protected $casts = [
		'region_id' => 'int'
	];

	protected $fillable = [
		'name',
		'description',
		'region_id'
	];
}
