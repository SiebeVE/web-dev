<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run () {
		DB::table('users')->insert([
			'name'     => "Siebe1",
			'email'    => "siebe@game1.com",
			'password' => bcrypt('wachtwoord'),
			'is_admin' => true,
			'verified' => true
		]);
		DB::table('users')->insert([
			'name'     => "Sam1",
			'email'    => "sam@game1.com",
			'password' => bcrypt('wachtwoord'),
			'verified' => true
		]);
		DB::table('users')->insert([
			'name'     => "Siebe2",
			'email'    => "siebe@game2.com",
			'password' => bcrypt('wachtwoord'),
			'verified' => true
		]);
		DB::table('users')->insert([
			'name'     => "Sam2",
			'email'    => "sam@game2.com",
			'password' => bcrypt('wachtwoord'),
			'verified' => true
		]);
		DB::table('users')->insert([
			'name'     => "Siebe3",
			'email'    => "siebe@game3.com",
			'password' => bcrypt('wachtwoord'),
			'verified' => true
		]);
		DB::table('users')->insert([
			'name'     => "Sam3",
			'email'    => "sam@game3.com",
			'password' => bcrypt('wachtwoord'),
			'verified' => true
		]);
	}
}
