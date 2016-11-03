<?php

use Illuminate\Database\Seeder;

class LotsOfFakeUsers extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    factory(App\User::class, random_int(128, 150))->create();
    }
}
