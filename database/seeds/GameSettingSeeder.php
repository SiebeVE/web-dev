<?php

use Illuminate\Database\Seeder;

class GameSettingSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run () {
		DB::table('game_settings')->insert([
			'name' => "periodStart",
			'data' => "2016-01-03",
			'type' => 'yyyy-mm-dd',
		]);

		DB::table('game_settings')->insert([
			'name' => "lengthOfPeriod",
			'data' => "4",
			'type' => 'months',
		]);

		DB::table('game_settings')->insert([
			'name' => "lengthOfUnsubscribePeriod",
			'data' => "7",
			'type' => 'days',
		]);

		DB::table('game_settings')->insert([
			'name' => "numberOfPlayers",
			'data' => "2",
		]);

		DB::table('game_settings')->insert([
			'name' => "lengthOfBattle",
			'data' => "24",
			'type' => 'hours',
		]);

		DB::table('game_settings')->insert([
			'name' => 'endSubscribeForCompetition',
			'data' => '',
		]);

		DB::table('game_settings')->insert([
			'name' => 'endRoundForCompetition',
			'data' => '',
		]);
	}
}
