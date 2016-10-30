<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Battle extends Model {
	use SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [ 'deleted_at' ];

	/**
	 * Get the picks of a battle
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function picks() {
		return $this->hasMany( 'App\Pick' );
	}

	/**
	 * Get the current users of this battle
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function cur_users() {
		return $this->hasMany( 'App\User' );
	}

	/**
	 * Get all users of this battle
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function users() {
		return $this->belongsToMany( 'App\User', 'picks' );
	}
}
