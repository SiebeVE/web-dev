<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pick extends Model
{
	use SoftDeletes;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		"ip_address",
		"pick",
		"battle_id"
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [ 'deleted_at' ];

	/**
	 * Get the battle of this pick
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function battle()
	{
		return $this->belongsTo('App\Battle');
	}

	/**
	 * Get the user of this pick
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function user()
	{
		return $this->belongsTo('App\User');
	}
}
