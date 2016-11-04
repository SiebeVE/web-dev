<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameSetting extends Model
{
	/**
	 * Get the row of given column
	 *
	 * @param $column
	 *
	 * @return mixed
	 */
	public function getData ($column) {
		$game_setting = GameSetting::where('name', $column)->firstOrFail();

		return $game_setting->data;
    }

	/**
	 * Set the data on given column
	 *
	 * @param $column
	 * @param $data
	 *
	 * @return mixed
	 */
	public function setData ($column, $data)
    {
	    $game_setting = GameSetting::where('name', $column)->firstOrFail();
	    $game_setting->data = $data;
	    $resultSave = $game_setting->save();
	    return $resultSave;
    }
}
