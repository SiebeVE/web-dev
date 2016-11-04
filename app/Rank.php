<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rank extends Model
{
	use SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'deleted_at',
		'period_start'
	];

	/**
	 * Get the user of this rank
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user () {
		return $this->belongsTo('\App\User');
	}

	/**
	 * Get the competition of this rank
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function competition () {
		return $this->belongsTo('\App\Competition');
	}
}
