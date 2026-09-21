<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Region
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 *
 * @package App\Models
 */
class Region extends Model
{
	protected $table = 'regions';
	public $timestamps = false;

	protected $fillable = [
		'name',
		'code'
	];
}
