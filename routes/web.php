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

Route::get('/', 'HomeController@welcome');

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::post('/home', 'HomeController@postAdmin');

Route::get('/register/confirm/{token}', 'Auth\RegisterController@confirmEmail');

Route::get('/battle/start', function () {
	\Illuminate\Support\Facades\Artisan::call('migrate:refresh', ['--seed' => true,]);
	//$battle = new BattleLogic();
	//$competition = $battle->start_competition();
	//$battle->start_battle($competition);
	dump(true);
	debug("");
});
Route::get('/battle/finish', function () {
	\Illuminate\Support\Facades\Artisan::call('check:competition');
	//$battle = new BattleLogic();
	//$competition = $battle->start_competition();
	//$battle->start_battle($competition);
	dump(true);
	debug("");
});
Route::get('/battle/picking', function () {
	$battle = new BattleLogic();
	$battle->play_battle_debug(0.02, 1);
	dump(true);
	debug("");
});
Route::get('/battle/end', function () {
	$battle = new BattleLogic();
	$battle->endRound(\App\Competition::firstOrFail());
	dump(true);
	debug("");
});

Route::get('/rank', "BattleController@getRank");

Route::get('/battle/{battle}', "BattleController@getBattle");
Route::get('/battle/{battle}/{pick}', "BattleController@postBattle");

Route::get('/competition/battle/{battle}', "BattleController@viewCompetitionBattle");
Route::get('/competition/cancel/{competitionId}', "BattleController@cancelCompetition");

Route::bind('battle', function ($value, $route) {
	// Id is hashed, so users can't guess the ids of other games, so now we need to decode it
	return \App\Battle::where('id', decodeHash($value)[0])->firstOrFail();
});

Route::bind('competitionId', function ($value, $route) {
	// Id is hashed, so users can't guess the ids of other games, so now we need to decode it
	return decodeHash($value, "comp")[0];
});