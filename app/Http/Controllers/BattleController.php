<?php

namespace App\Http\Controllers;

use App\Battle;
use App\BattleLogic\BattleLogic;
use App\Pick;
use App\User;
use Illuminate\Http\Request;

class BattleController extends Controller
{

	private $battleLogic;

	public function __construct () {
		$this->middleware("auth");
		$this->middleware("battlePermission", [
			'only' => [
				"getBattle",
				"postBattle"
			]
		]);

		$this->battleLogic = new BattleLogic();
	}

	public function getBattle (Battle $battle) {

		return view("battle.enter");
	}

	public function postBattle (Battle $battle, $pick, Request $request) {
		$pickDB = $this->battleLogic->play_battle($battle, $pick);

		flashToastr("success", "U heeft " . $pick . " gespeeld.", "Hoera, je hebt gespeeld, binnenkort krijg je de uitslag!");

		return redirect('/home');
	}

	public function cancelCompetition ($competitionId) {
		$this->battleLogic->unsubscribe_competition($competitionId);

		
	}

	public function viewCompetitionBattle (Battle $battle) {
		//Get last battle
		while(count($battle->with_retake()) > 0)
		{
			$battle = $battle->with_retake();
		}

		if($battle->winner_id == NULL)
		{
			abort(401, "The round has not yet finished, not allowed to view the outcome.");
		}

		$outcome = $this->battleLogic->getBattleOutcome($battle);
		$userNames = "";
		for ($counter = 0; $counter < count($outcome[0]); $counter++)
		{
			$userNames .= $outcome[0][$counter]["user"]["name"];
			if($counter + 1 < count($outcome[0]))
			{
				$userNames .= " en ";
			}
			else if($counter >= count($outcome[0]))
			{
				$userNames .= ", ";
			}
		}
		debug($outcome);

		return view("battle.outcome", compact("outcome", "userNames"));
	}
}
