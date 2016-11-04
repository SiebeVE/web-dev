<?php

namespace App\Http\Controllers;

use App\Battle;
use App\BattleLogic\BattleLogic;
use App\Notifications\UnsubscribeCompetition;
use App\Pick;
use App\Rank;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

		flashToastr("success", "Uitgeschreven", "U bent succesvol uitgeschreven!");

		Auth::user()->notify(new UnsubscribeCompetition());

		return redirect('/home');
	}

	public function viewCompetitionBattle (Battle $battle) {
		//Get last battle
		while (count($battle->with_retake()) > 0) {
			$battle = $battle->with_retake()->last();
		}

		if ($battle->winner_id == NULL) {
			abort(401, "The round has not yet finished, not allowed to view the outcome.");
		}

		$outcome = $this->battleLogic->getBattleOutcome($battle);
		$userNames = "";
		for ($counter = 0; $counter < count($outcome[0]); $counter ++) {
			$userNames .= $outcome[0][ $counter ]["user"]["name"];
			if ($counter + 2 == count($outcome[0])) {
				$userNames .= " en ";
			}
			else if ($counter + 1 < count($outcome[0])) {
				$userNames .= ", ";
			}
		}
		debug($outcome);

		return view("battle.outcome", compact("outcome", "userNames"));
	}

	public function getRank () {
		Carbon::setLocale('nl');

		$ranks = Rank::with('user')->get();

		$rankPerPeriod = [];
		foreach ($ranks as $rank) {
			$periodStart = $rank->period_start->format('d-m-Y');
			if ( ! array_key_exists($periodStart, $rankPerPeriod)) {
				$rankPerPeriod[ $periodStart ] = [];
			}

			$rankPerPeriod[ $periodStart ][] = $rank;
		}

		return view('rank', ["ranks" => $rankPerPeriod]);
	}
}
