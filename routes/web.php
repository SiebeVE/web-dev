<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

use App\BattleLogic\BattleLogic;

Route::get('/', function () {
	return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('/register/confirm/{token}', 'Auth\RegisterController@confirmEmail');

Route::get('/battle/start', function () {
	\Illuminate\Support\Facades\Artisan::call('migrate:refresh', ['--seed' => true,]);
	$battle = new BattleLogic();
	$competition = $battle->start_competition();
	$battle->start_battle($competition);
	dump(true);
	debug("");
});
Route::get('/battle/picking', function () {
	$battle = new BattleLogic();
	$battle->play_battle_debug(0.005, 1);
	dump(true);
	debug("");
});
Route::get('/battle/end', function () {
	$battle = new BattleLogic();
	$battle->endRound(\App\Competition::firstOrFail());
	dump(true);
	debug("");
});

Route::get('/battle/{battle}', "BattleController@getBattle");
Route::get('/battle/{battle}/{pick}', "BattleController@postBattle");

Route::bind('battle', function ($value, $route) {
	// Id is hashed, so users can't guess the ids of other games, so now we need to decode it
	return \App\Battle::where('id', decodeHash($value)[0])->firstOrFail();
});