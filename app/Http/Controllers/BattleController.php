<?php

namespace App\Http\Controllers;

use App\Battle;
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
		$legitPicks = [
			"paper",
			"scissors",
			"rock"
		];

		$pickWins = [
			"paper"    => "rock",
			"rock"     => "scissors",
			"scissors" => "paper"
		];

		// Fetch the users current ip address
		$ipAddress = request()->ip();
		// Check if the pick is leggit
		if ( ! (in_array($pick, $legitPicks))) {
			// The pick is not legit, so abort
			abort(400, "The requested pick is not an option");
		}

		// Create a new pick, linked to the users
		$user = $request->user();
		$battle_id = $battle->decodedId();
		$pickDB = $user->picks()->create([
			"ip_address" => $ipAddress,
			"pick"       => $pick,
			"battle_id"  => $battle_id,
		]);

		$pickDB->save();

		// Set the battle id of the user to null, because he played his part
		$user->battle_id = NULL;
		$user->save();

		// Check if their is still a user playing
		$users_playing = User::where("battle_id", $battle_id)->get();
		if (count($users_playing) == 0) {
			// No more users playing, so check the winner and save it in the battle
			// Fetch all picks with battle id
			$picks = Pick::where("battle_id", $battle_id)->get();

			$winner_pick = NULL;
			$winner_id = NULL;
			$winner_array = [];
			foreach ($picks as $pick) {
				if ( ! key_exists($pick->pick, $winner_array)) {
					$winner_array[ $pick->pick ] = [];
				}
				$winner_array[ $pick->pick ][] = $pick->user_id;
			}
			// Check if their could be a winner
			switch (count($winner_array)) {
				// Everyone picked the same, reset the battle
				case 1:
					$this->resetBattle($battle, $picks, $battle_id);
					break;
				// Huray, their is a winner
				case 2:
					// Check who has won
					$winner_keys = array_keys($winner_array);

					$win_key = $winner_keys[1];
					$lose_key = $winner_keys[0];

					if ($pickWins[ $winner_keys[0] ] == $winner_keys[1]) {
						// Key 0 has won
						$win_key = $winner_keys[0];
						$lose_key = $winner_keys[1];
					}

					if (count($winner_array[ $win_key ]) > 1) {
						// More then one winner, play again with remaining players
						$this->resetBattle($battle, $picks, $battle_id);

						// Now set the losing player battle_id to null
						foreach ($winner_array[ $lose_key ] as $loserId) {
							$user = User::where('id', $loserId)->firstOrFail();
							$user->battle_id = NULL;
							$user->save();
						}
					}
					else {
						// One winner
						//dump($winner_array[ $win_key ][0]);
						$winner_id = $winner_array[ $win_key ][0];
					}
					break;
				// All the possibilities where picked, reset the battle
				case 3:
					$this->resetBattle($battle, $picks, $battle_id);
					break;
			}
			$battle->winner_id = $winner_id;
			$battle->save();
		}
		flashToastr("success", "U heeft " . $pick . " gespeeld.", "Hoera, je hebt gespeeld, binnenkort krijg je de uitslag!");

		return redirect('/home');
	}

	/**
	 * Reset (make new) the battle when it is a draw
	 *
	 * @param $battle
	 * @param $picks
	 * @param $battle_id
	 */
	private function resetBattle ($battle, $picks, $battle_id) {
		$newBattle = $battle->replicate();

		$newBattle->is_retake_of = $battle_id;
		$newBattle->save();

		foreach ($picks as $pickBattle) {
			$user = $pickBattle->user;

			$user->battle_id = $newBattle->decodedId();
			$user->save();
		}
	}
}
