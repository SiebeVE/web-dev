<?php

namespace App\Http\Controllers;

use App\Battle;
use App\BattleLogic\BattleLogic;
use App\Pick;
use App\User;
use Illuminate\Http\Request;

class BattleController extends Controller
{

	public function __construct () {
		$this->middleware("auth");
		$this->middleware("battlePermission", [
			'only' => [
				"getBattle",
				"postBattle"
			]
		]);
	}

	public function getBattle (Battle $battle) {

		return view("battle.enter");
	}

	public function postBattle (Battle $battle, $pick, Request $request) {

		$battleLogic = new BattleLogic();

		$pickWins = [
			"paper"    => "rock",
			"rock"     => "scissors",
			"scissors" => "paper"
		];

		$pickDB = $battleLogic->play_battle($battle, $pick, NULL, 20000);

		flashToastr("success", "U heeft " . $pick . " gespeeld.", "Hoera, je hebt gespeeld, binnenkort krijg je de uitslag!");

		return redirect('/home');
	}
}
