<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Competition extends Model
{
	use SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'deleted_at',
		'start_date',
		'period_start'
	];

	/**
	 * Get the users which are playing in this competition
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function playing_users () {
		return $this->hasMany('\App\PlayingUser');
	}

	/**
	 * Get the ranking of this competition
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function ranks () {
		return $this->hasMany('\App\Rank');
	}

	/**
	 * Get the winner of this competition
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function winner () {
		return $this->belongsTo('\App\User');
	}

	/**
	 * Get all battles of this competition
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function battle () {
		return $this->hasMany('\App\Battle');
	}
}
