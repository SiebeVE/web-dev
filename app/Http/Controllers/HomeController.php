<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'auth' );
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		$user          = Auth::user();
		$currentBattle = $user->cur_battle;

		//dump($currentBattle);

		$opponents     = $currentBattle ? $currentBattle->getOpponents( $user ) : NULL;

		//dump($opponents);

		return view( 'home', [
			"battle"    => $currentBattle,
			"opponents" => $opponents,
		] );
	}
}
