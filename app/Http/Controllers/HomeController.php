<?php

namespace App\Http\Controllers;

use App\BattleLogic\BattleLogic;
use App\Competition;
use App\GameSetting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct () {
		$this->middleware('auth')->except('welcome');
	}

	public function welcome () {
		return view('welcome', ["competitions" => Competition::get()]);
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index () {
		$user = Auth::user();
		$varToView = [];
		if ($user->is_admin) {
			$gameSettings = GameSetting::get();
			$competitions = Competition::get();
			$users = User::get();
			$varToView = [
				"gameSettings" => $gameSettings,
				"competitions" => $competitions,
				"users"        => $users,
			];
		}
		else {
			$currentBattle = $user->cur_battle;
			//dump($currentBattle);
			$opponents = $currentBattle ? $currentBattle->getOpponents($user) : NULL;
			$varToView = [
				"battle"         => $currentBattle,
				"opponents"      => $opponents,
				"previousBattle" => (new BattleLogic())->getUserOutcome(),
			];
			//dump($opponents);
		}

		return view('home', $varToView);
	}

	public function postAdmin (Request $request) {
		$user = Auth::user();
		if ( ! $user->is_admin) {
			abort(403, "Elaba, das hier enkel voor admins");
		}

		$gameSettings = new GameSetting();

		foreach ($request->all() as $key => $field) {
			if ($key != "_token") {
				$gameSettings->setData($key, $field);
			}
		}

		flashToastr('success', 'Geüpdatet', "De waarden zijn succesvol aangepast!");

		return redirect('/home');
	}
}
