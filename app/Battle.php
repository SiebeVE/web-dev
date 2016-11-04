<?php

namespace App;

use Hashids\Hashids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Battle extends Model
{
	use SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'deleted_at',
	];

	/**
	 * The attributes that are mass assignable
	 *
	 * @var array
	 */
	protected $fillable = [
		"round",
		"competition_id"
	];

	/**
	 * Get the id as a hashed string
	 *
	 * @param $id
	 *
	 * @return Hashids|string
	 */
	public function getIdAttribute ($id) {
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
	public function decodedId () {
		return decodeHash($this->id)[0];
	}

	/**
	 * Get the picks of a battle
	 *
	 * @return mixed
	 */
	public function picks () {
		// Cant use relations because of hashed battle id
		$picks = Pick::where('battle_id', $this->decodedId())->get();

		return $picks;
	}

	/**
	 * Get the current users of this battle
	 *
	 * @return mixed
	 */
	public function cur_users () {
		// Cant use relations because of hashed battle id
		$users = User::where('battle_id', $this->decodedId())->get();

		return $users;
	}

	/**
	 * Get the opponents (not the user it self) of this battle, including all ready played
	 *
	 * @param null $user
	 *
	 * @return array
	 */
	public function getOpponents ($user = NULL) {
		$currentUser = $user == null ? Auth::user() : $user;
		$opponents = [];

		$users = $this->cur_users();
		$picks = $this->picks();

		foreach ($users as $user) {
			if ($user->id != $currentUser->id) {
				$opponents[] = $user;
			}
		}

		foreach ($picks as $pick) {
			if ($pick->user_id != $currentUser->id) {
				$opponents[] = $pick->user;
			}
		}

		return $opponents;
	}

	/**
	 * Get all users of this battle
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function users () {
		return $this->belongsToMany('App\User', 'picks');
	}

	/**
	 * Get the possible retake of this battle
	 *
	 * @return \App\Battle
	 */
	public function with_retake () {
		// Cant use relations because of hashed battle id
		$battle = Battle::where('is_retake_of', $this->decodedId())->get();
		return $battle;
	}

	/**
	 * Get the battle which has been retaken
	 *
	 * @return \App\Battle
	 */
	public function get_retake_of () {
		// Cant use relations because of hashed battle id
		$battle = Battle::where('id', $this->is_retake_of)->get();
		return $battle;
	}

	/**
	 * Get the competition of this battle
	 *
	 * @return mixed
	 */
	public function competition () {
		// Cant use relations because of hashed battle id
		$competition = Competition::where('id', $this->decodedId())->get();

		return $competition;
	}
}
