<?php
/**
 * Created by PhpStorm.
 * User: Siebe
 * Date: 31/10/2016
 * Time: 12:37
 */

namespace App\BattleLogic;

use App\Battle;
use App\Competition;
use App\Pick;
use App\PlayingUser;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * This class controls my game
 *
 * Class BattleLogic
 * @package App\BattleLogic
 */
class BattleLogic
{
	private $currentPeriodStart, $numberOfPlayers, $lengthOfBattle, $round, $legitPicks, $pickWins;

	public function __construct () {
		// Fetch the current period start date
		$this->currentPeriodStart = Carbon::now();
		$this->numberOfPlayers = 2;
		$this->lengthOfBattle = 24;
		$this->round = 1;
		$this->legitPicks = [
			"paper",
			"scissors",
			"rock"
		];
		$this->pickWins = [
			"paper"    => "rock",
			"rock"     => "scissors",
			"scissors" => "paper"
		];
	}

	/**
	 * Get a random weapon
	 *
	 * @return mixed
	 */
	private function randomPick () {
		return $this->legitPicks[ random_int(0, count($this->legitPicks) - 1) ];
	}

	/**
	 * Method so all the users play the battle with a percentage in which they fail
	 *
	 * @param $failChance
	 * @param $competitionId
	 */
	public function play_battle_debug ($failChance, $competitionId) {
		// Get all playing users
		$playingUsers = PlayingUser::where('competition_id', $competitionId)->get();

		$retakeBattles = $this->play_battle_given_users_debug($playingUsers, $failChance);

		while (count($retakeBattles) > 0) {
			debug("Starting retakes");
			$userArray = collect([]);

			foreach ($retakeBattles as $retakeBattle) {
				debug("Add users from retake battle " . $retakeBattle->decodedId() . " to array");
				$userArray = $userArray->merge($retakeBattle->cur_users());
			}
			debug("Play the retakes");
			$retakeBattles = $this->play_battle_given_users_debug($userArray, $failChance, true);
		}
		debug("Finished playing the battles");
	}

	/**
	 * Play the battles of the users in the given array
	 *
	 * @param $playingUsers
	 * @param $failChance
	 *
	 * @param bool $array_are_users
	 *
	 * @return array
	 */
	private function play_battle_given_users_debug ($playingUsers, $failChance, $array_are_users = false) {
		$retakeBattles = [];
		foreach ($playingUsers as $playingUser) {
			// Fake so specific users don't participate
			$has_failed = random_int(0, 100) < $failChance * 100;
			// Get the id of the battle
			//dump($playingUser);
			//dump($playingUser->user);
			$user = $array_are_users ? $playingUser : $playingUser->user;
			$battle_id = $user->battle_id;
			// Generate a random pick
			$pick = $this->randomPick();

			if ( ! $has_failed) {
				debug("User " . $user->name . " (" . $user->id . ") picked " . $pick . " for battle " . $battle_id);
				// Play the battle
				$battle = Battle::where('id', $battle_id)->firstOrFail();
				$play_battle = $this->play_battle($battle, $pick, $user);
				switch ($play_battle[0]) {
					case 1:
					case 3:
					case 4:
						// Rematch
						debug("A rematch is requested fot battle " . $battle_id);
						$retakeBattle = $play_battle[1];
						debug("Retake id: " . $retakeBattle->decodedId());
						$retakeBattles[] = $retakeBattle;
						break;
				}
			}
			else {
				debug("User " . $user->name . " (" . $user->id . ") failed to pick");
			}
		}

		return $retakeBattles;
	}


	/**
	 * Prepare the database for a new competition
	 *
	 * @return Competition
	 */
	public function start_competition () {
		// Make a new competition
		$newCompetition = new Competition();
		$newCompetition->start_date = Carbon::now();
		$newCompetition->period_start = $this->currentPeriodStart;

		$newCompetition->save();

		// Add the users for this competition, by default everyone, except Admins
		$users = User::where("is_admin", false)->get();
		foreach ($users as $user) {
			$user->playingUsers()->create([
				"competition_id" => $newCompetition->id
			]);
		}

		return $newCompetition;
	}

	public function end_competition () {

	}

	/**
	 * Initialize all the battles for the currently playing users
	 *
	 * @param Competition $competition
	 *
	 * @return bool
	 */
	public function start_battle (Competition $competition) {
		// Get all the playing users
		$playingUsers = PlayingUser::with("user")->where('competition_id', $competition->id)->inRandomOrder()->get();

		$usedUsers = "";

		// Check if their is a user single
		if (count($playingUsers) % $this->numberOfPlayers == 1) {
			debug("there is a single user, create battle with one extra");
			// Create a battle with one more user then intended
			$playingUserArray = [];


			for ($counter = $this->numberOfPlayers + 1; $counter != 0; $counter --) {
				$lastPlayingUser = $playingUsers->last();
				$usedUsers .= $lastPlayingUser->user->name . " (" . $lastPlayingUser->user->id . "), ";
				$playingUserArray[] = $lastPlayingUser;
				$playingUsers->pop();
			}
			$battle = $this->create_battle($playingUserArray, $this->lengthOfBattle, $this->round, $competition);
			debug("Battle " . $battle->decodedId() . " created with players " . $usedUsers);
			$usedUsers = "";
		}

		debug("Their are " . count($playingUsers) . " not yet in a battle.");

		$playingUserArray = [];
		while ($playingUsers->last() != NULL) {
			// Populate the array with the last user
			$lastPlayingUser = $playingUsers->last();
			$usedUsers .= $lastPlayingUser->user->name . " (" . $lastPlayingUser->user->id . "), ";
			$playingUserArray[] = $lastPlayingUser;
			$playingUsers->pop();
			// Check if the next battle has to be created
			if (count($playingUserArray) == $this->numberOfPlayers) {
				// The array is full, the same number as the number of players
				debug("The array is full, create a new battle");
				$battle = $this->create_battle($playingUserArray, $this->lengthOfBattle, $this->round, $competition);
				debug("Battle " . $battle->decodedId() . " created with players " . $usedUsers);
				$usedUsers = "";
				// Empty it out for the next one
				$playingUserArray = [];
			}
			debug("Their are " . count($playingUsers) . " not yet in a battle.");
		}

		if (count($playingUserArray) != 0) {
			// When their are still users which aren't in a battle
			debug("Create final battle with last users");
			$battle = $this->create_battle($playingUserArray, $this->lengthOfBattle, $this->round, $competition);
			debug("Battle " . $battle->decodedId() . " created with players " . $usedUsers);
		}

		debug("Finished");

		return true;
	}

	/**
	 * Lets a user play a battle, picked a weapon
	 *
	 * @param Battle $battle
	 * @param $pick
	 *
	 * @param User $userDEBUG
	 *
	 * @return mixed
	 * @internal param $battle_id
	 */
	public function play_battle (Battle $battle, $pick, User $userDEBUG = NULL) {
		// Fetch the users current ip address
		$ipAddress = request()->ip();
		// Check if the pick is leggit
		if ( ! (in_array($pick, $this->legitPicks))) {
			// The pick is not legit, so abort
			abort(400, "The requested pick is not an option");
		}

		$battle_id = $battle->decodedId();
		// Create a new pick, linked to the users
		$user = $userDEBUG == NULL ? Auth::user() : $userDEBUG;
		$pickDB = $user->picks()->create([
			"ip_address" => $ipAddress,
			"pick"       => $pick,
			"battle_id"  => $battle_id,
		]);

		$pickDB->save();

		// Set the battle id of the user to null, because he played his part
		$user->battle_id = NULL;
		$user->save();

		return $this->validate_battle($battle, $battle_id);
	}

	public function endRound (Competition $competition) {
		// Fetch last round of competition
		$battleComp = $competition->battle()->orderBy('round', 'DESC')->get();
		$winner_ids = array_pluck($battleComp->toArray(), 'winner_id');
		//dump($winner_ids);
		$lastBattle = $battleComp->first();
		$round = $lastBattle->round;
		debug("Last round is " . $round);
		// Disqualify all users which haven't played
		$stillPlayingUsers = $competition->playing_users;
		dump($stillPlayingUsers);
		debug("Checking for players that hasn't played");
		foreach ($stillPlayingUsers as $stillPlayingUser) {
			// Only users that have won will be in the playing users table
			// But we need to let the persons win that has picked for the battle
			if ( ! in_array($stillPlayingUser->user->id, $winner_ids) && $stillPlayingUser->user->battle_id != NULL) {
				debug("Player (" . $stillPlayingUser->user->id . ") " . $stillPlayingUser->user->name . " still hasn't played, so remove him.");
				$battle_id = $stillPlayingUser->user->battle_id;
				$stillPlayingUser->user->battle_id = NULL;
				$stillPlayingUser->user->save();
				$stillPlayingUser->delete();
				debug("Now check the battle (" . $battle_id . ")");
				$this->validate_battle(Battle::where('id', $battle_id)->firstOrFail(), $battle_id);
			}
		}

		if(count($battleComp) == 1)
		{
			// The final battle has been played
			$this->end_competition();
		}
		else
		{
			// The final battle hasn't been played yet
			$this->round = $round + 1;
			$this->start_battle($competition);
		}
	}

	/**
	 * Check if the battle has finished
	 * If finished, then check the winner
	 *
	 * Returns 0 if not yet finished
	 * Returns 1 if reset (all the same)
	 * Returns 2 if their is a winner
	 * Returns 3 if reset (all different)
	 * Returns 4 if rematch (2 or more winners)
	 *
	 * @param Battle $battle
	 * @param $battle_id
	 *
	 * @return array
	 */
	private function validate_battle (Battle $battle, $battle_id) {
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
			$multiple_winner = false;
			$newBattle = NULL;
			switch (count($winner_array)) {
				// Everyone picked the same, reset the battle
				case 1:
					$lonelyKey = array_keys($winner_array)[0];
					if (count($winner_array[ $lonelyKey ]) == 1) {
						debug("Player is alone, so has won this battle");
						$winner_id = $winner_array[ $lonelyKey ][0];
						debug("The winner of battle " . $battle_id . " is " . $winner_id);
					}
					else {
						debug("Players of battle " . $battle_id . " picked all the same, reset!");
						$newBattle = $this->resetBattle($battle, $picks, $battle_id);
					}
					break;
				// Huray, their is a winner
				case 2:
					// Check who has won
					$winner_keys = array_keys($winner_array);

					$win_key = $winner_keys[1];
					$lose_key = $winner_keys[0];

					if ($this->pickWins[ $winner_keys[0] ] == $winner_keys[1]) {
						// Key 0 has won
						$win_key = $winner_keys[0];
						$lose_key = $winner_keys[1];
					}

					if (count($winner_array[ $win_key ]) > 1) {
						// More then one winner, play again with remaining players
						debug("The battle " . $battle_id . " is not yet determined (multiple winners), a rematch is required.");
						$newBattle = $this->resetBattle($battle, $picks, $battle_id);
						$multiple_winner = true;
					}
					else {
						// One winner
						$winner_id = $winner_array[ $win_key ][0];
						debug("The winner of battle " . $battle_id . " is user " . $winner_id);
						// Remove other from playing users table

					}
					// Remove the losers from the playing users table
					foreach ($winner_array[ $lose_key ] as $loserId) {
						$user = User::where('id', $loserId)->firstOrFail();
						if (count($winner_array[ $win_key ]) > 1) {
							// Now set the losing player battle_id to null because the battle was reset
							debug($user->name . " has lost, so isn't playing in the rematch");
							$user->battle_id = NULL;
							$user->save();
						}
						debug($user->name . " has lost battle " . $battle_id . ", so remove him from the playing users table");
						$user->playingUsers()->delete();
					}
					break;
				// All the possibilities where picked, reset the battle
				case 3:
					debug("Players of battle " . $battle_id . " picked all different, reset!");
					$newBattle = $this->resetBattle($battle, $picks, $battle_id);
					break;
			}
			$battle->winner_id = $winner_id;
			$battle->save();

			return [$multiple_winner ? 4 : count($winner_array), $newBattle];
		}
		else {
			debug("The battle (" . $battle_id . ") is not yet finished...");
		}

		return [0, NULL];
	}

	/**
	 * Create a battle and set the proper columns for the user
	 *
	 * @param $playingUsers
	 * @param $lengthBattleInHours
	 * @param $round
	 *
	 * @param Competition $competition
	 *
	 * @return Battle
	 */
	private function create_battle ($playingUsers, $lengthBattleInHours, $round, Competition $competition) {
		// Battle starts right now
		$start_date = Carbon::now();
		// Create the battle
		$newBattle = Battle::create([
			"start_date"     => $start_date,
			"end_date"       => $start_date->addHour($lengthBattleInHours),
			"round"          => $round,
			"competition_id" => $competition->id,
		]);
		$newBattle->save();


		foreach ($playingUsers as $playingUser) {
			// Fetch the users for this battle and set the battle id
			$user = $playingUser->user;
			$user->battle_id = $newBattle->decodedId();
			$user->save();
		}

		return $newBattle;
	}

	/**
	 * Reset (make new) the battle when it is a draw
	 *
	 * @param $battle
	 * @param $picks
	 * @param $battle_id
	 *
	 * @return mixed
	 */
	private function resetBattle ($battle, $picks, $battle_id) {
		// Make copy of battle
		$newBattle = $battle->replicate();

		// Set it as a retake
		$newBattle->is_retake_of = $battle_id;
		$newBattle->save();

		foreach ($picks as $pickBattle) {
			// Re-invite the users
			$user = $pickBattle->user;

			$user->battle_id = $newBattle->decodedId();
			$user->save();
		}

		return $newBattle;
	}
}