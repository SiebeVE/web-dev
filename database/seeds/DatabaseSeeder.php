<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run () {
		//$this->call(BattleTableSeeder::class);
		$this->call(UsersTableSeeder::class);
		//$this->call(LotsOfFakeUsers::class);
		$this->call(GameSettingSeeder::class);
	}
}
