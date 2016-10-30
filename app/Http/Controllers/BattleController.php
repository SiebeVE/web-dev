<?php

namespace App\Http\Controllers;

use App\Battle;
use App\Pick;
use App\User;
use Illuminate\Http\Request;

class BattleController extends Controller {

	public function __construct() {
		$this->middleware("auth");
		$this->middleware( "battlePermission", [
			'only' => [
				"getBattle",
				"postBattle"
			]
		] );
	}

	public function getBattle( Battle $battle ) {

		return view( "battle.enter" );
	}

	public function postBattle( Battle $battle, $pick, Request $request ) {
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
		if ( ! ( in_array( $pick, $legitPicks ) ) ) {
			// The pick is not legit, so abort
			abort( 400, "The requested pick is not an option" );
		}

		// Create a new pick, linked to the users
		$user      = $request->user();
		$battle_id = $battle->decodedId();
		$pickDB    = $user->picks()->create( [
			"ip_address" => $ipAddress,
			"pick"       => $pick,
			"battle_id"  => $battle_id,
		] );

		$pickDB->save();

		// Set the battle id of the user to null, because he played his part
		$user->battle_id = NULL;
		$user->save();

		// Check if their is still a user playing
		$users_playing = User::where( "battle_id", $battle_id )->get();
		if ( count( $users_playing ) == 0 ) {
			// No more users playing, so check the winner and save it in the battle
			// Fetch all picks with battle id
			$picks = Pick::where( "battle_id", $battle_id )->get();

			$previousPick = NULL;
			$winner_id = NULL;
			foreach ( $picks as $pick ) {
				dump($pick);
				// Set the first pick
				if ( $previousPick == NULL ) {
					$previousPick = $pick;
					continue;
				}

				// Check which case
				switch ($pick->pick)
				{
					// The previous is the winner
					case $pickWins[$previousPick->pick]:
						$winner_id = $previousPick->user_id;
						break;
					// It is a draw
					case $previousPick->pick:
						// Reset this battle
						$newBattle = $battle->replicate();
						$newBattle->is_retake_of = $battle_id;
						$newBattle->save();
						foreach ($picks as $pickBattle)
						{
							$user = $pickBattle->user;
							dump($user);
							$user->battle_id = $newBattle->decodedId();
							$user->save();
						}
						break;
					// The new user is the winner
					default:
						$winner_id = $pick->user_id;
						break;
				}

				$battle->winner_id = $winner_id;
				$battle->save();

				$previousPick = $pick;
			}
		}

		return view( "battle.enter" );
	}
}
