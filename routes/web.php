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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('/register/confirm/{token}', 'Auth\RegisterController@confirmEmail');

Route::get('/battle/{battle}', "BattleController@getBattle");
Route::get('/battle/{battle}/{pick}', "BattleController@postBattle");

Route::bind('battle', function($value, $route){
	// Id is hashed, so users can't guess the ids of other games, so now we need to decode it
	return \App\Battle::where('id', decodeHash($value)[0])->firstOrFail();
});