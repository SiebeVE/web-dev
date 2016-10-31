<?php

use Illuminate\Database\Seeder;

class BattleTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run () {
		DB::table('battles')->insert([
			"start_date" => new \Carbon\Carbon(),
			"end_date"   => new \Carbon\Carbon(),
			"round"      => 1
		]);
		DB::table('battles')->insert([
			"start_date" => new \Carbon\Carbon(),
			"end_date"   => new \Carbon\Carbon(),
			"round"      => 1
		]);
		DB::table('battles')->insert([
			"start_date" => new \Carbon\Carbon(),
			"end_date"   => new \Carbon\Carbon(),
			"round"      => 1
		]);
	}
}
