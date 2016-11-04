<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
	use Notifiable;
	use SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [ 'deleted_at' ];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'email',
		'is_admin',
		'password',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * Function that is standard execute on events
	 */
	public static function boot()
	{
		parent::boot();

		//Executed when a new user is made
		static::creating(function ($user)
		{
			// Set the token for an email
			$user->token_mail = str_random(30);
		});
	}

	/**
	 * Get the picks of a user
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function picks()
	{
		return $this->hasMany('App\Pick');
	}

	/**
	 * Get the current battle of a user
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function cur_battle()
	{
		return $this->belongsTo('App\Battle', 'battle_id');
	}

	/**
	 * Get all battles of a user
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function battles(){
		return $this->belongsToMany('App\Battle', 'picks');
	}

	/**
	 * Get the ranks of this user
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function ranks () {
		return $this->hasMany('\App\Rank');
	}

	/**
	 * Get the competitions that the user has won
	 * 
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function wins () {
		return $this->hasMany('\App\Competition');
	}

	/**
	 * Get the current playing competitions
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function playingUsers () {
		return $this->hasMany('\App\PlayingUser');
	}

	/**
	 * Handle database when the email is confirmed
	 */
	public function confirmEmail()
	{
		$this->verified = true;
		$this->token_mail = NULL;

		$this->save();
	}
}
