<?php

namespace App;

use Hashids\Hashids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Battle extends Model {
	use SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'deleted_at',
		'start_date',
		'end_date',
	];

	/**
	 * Get the id as a hashed string
	 *
	 * @param $id
	 *
	 * @return Hashids|string
	 */
	public function getIdAttribute($id)
	{
		// When we fetch the id, we want it hashed, so the user can't guess the next game and fuck my game
		$hashedId = new Hashids(env("HASH_SECRET", "MySecretKey"), 15);
		$hashedId = $hashedId->encode($id);

		return $hashedId;
	}

	/**
	 * Get the decoded ID
	 *
	 * @return array
	 */
	public function decodedId(){
		return decodeHash($this->id)[0];
	}

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

	/**
	 * Get the possible retake of this battle
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function with_retake(){
		return $this->hasOne('\App\Battle', 'is_retake_of', 'id');
	}

	/**
	 * Get the battle which has been retaken
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function retake_of(){
		return $this->belongsTo('\App\Battle', 'id', 'is_retake_of');
	}
}
