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
use App\GameSetting;
use App\Notifications\BattleLoss;
use App\Notifications\BattlePlay;
use App\Notifications\BattleRetake;
use App\Notifications\BattleStarted;
use App\Notifications\BattleWin;
use App\Notifications\CompetitionStarted;
use App\Notifications\WonCompetition;
use App\Pick;
use App\PlayingUser;
use App\Rank;
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
	private $gameSettings;

	public function __construct () {
		// Fetch the current period start date
		$this->gameSettings = new GameSetting();
		$originalPeriodStart = $this->gameSettings->getData("periodStart");
		$startPeriod = new Carbon($originalPeriodStart);
		$lengthOfPeriod = $this->gameSettings->getData("lengthOfPeriod");
		$relativePeriodCounter = floor($startPeriod->diffInMonths(Carbon::now()) / $lengthOfPeriod);
		$this->currentPeriodStart = $startPeriod->addMonths($lengthOfPeriod * $relativePeriodCounter);
		$this->numberOfPlayers = $this->gameSettings->getData("numberOfPlayers");
		$this->lengthOfBattle = $this->gameSettings->getData("lengthOfBattle");
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
			$user->notify(new CompetitionStarted($newCompetition));
		}

		return $newCompetition;
	}

	/**
	 * The user wishes not to play in the competition
	 *
	 * @param User $user
	 * @param $competition_id
	 */
	public function unsubscribe_competition ($competition_id, User $user = NULL) {
		if ($user == NULL) {
			$user = Auth::user();
		}
		$playing_user = PlayingUser::where([
			['user_id', $user->id],
			['competition_id', $competition_id]
		])->firstOrFail();
		$playing_user->delete();
		$user->battle_id = NULL;
		$user->save();
	}

	/**
	 * Function to set the final things in the database after a competition has been finished
	 *
	 * @param Competition $competition
	 * @param PlayingUser $winningUser
	 */
	public function end_competition (Competition $competition, PlayingUser $winningUser = NULL) {
		$competition->has_finished = true;
		$competition->save();
		if ($winningUser != NULL) {
			// Set the competitions winner id
			$competition->winner_id = $winningUser->user_id;
			$competition->save();

			// Remove the user from the Playing users database
			$winningUser->delete();
			debug("The competition was finished, player " . $winningUser->user->name . " (" . $winningUser->user_id . ") has won.");

			$rank = new Rank();
			$rank->user_id = $winningUser->user_id;
			$rank->competition_id = $competition->id;
			$rank->period_start = $this->currentPeriodStart;
			$rank->save();

			$notification = new WonCompetition($competition->id);
			dump($notification);
			// Notify the winning user
			$winningUser->user->notify($notification);
		}
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

		// Set the battle start date in the competition
		$competition->battle_start_date = Carbon::now();
		$competition->save();

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
			$battle = $this->create_battle($playingUserArray, $this->round, $competition);
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
				$battle = $this->create_battle($playingUserArray, $this->round, $competition);
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
			$battle = $this->create_battle($playingUserArray, $this->round, $competition);
			debug("Battle " . $battle->decodedId() . " created with players " . $usedUsers);
		}

		debug("Finished");

		return true;
	}

	/**
	 * Create a battle and set the proper columns for the user
	 *
	 * @param $playingUsers
	 * @param $round
	 *
	 * @param Competition $competition
	 *
	 * @return Battle
	 */
	private function create_battle ($playingUsers, $round, Competition $competition) {
		// Create the battle
		$newBattle = Battle::create([
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

		foreach ($playingUsers as $playingUser) {
			// Notify all users
			$playingUser->user->notify(new BattleStarted($newBattle, $playingUser->user));
		}

		return $newBattle;
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

		$validate_battle = $this->validate_battle($battle, $battle_id, $user);

		$user->notify(new BattlePlay($battle, $pick));

		return $validate_battle;
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
	 * @param User $user
	 *
	 * @return array
	 *
	 */
	private function validate_battle (Battle $battle, $battle_id, User $user) {
		// Check if their is still a user playing
		$users_playing = User::where("battle_id", $battle_id)->get();
		if (count($users_playing) == 0 && Battle::where('id', $battle_id)->firstOrfail()->is_checked_by == NULL) {
			// Set it so only one user can validate the battle
			$battle->is_checked_by = $user->id;
			$battle->save();
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
						// Notify the user it has won
						User::where('id', $winner_id)->firstOrFail()->notify(new BattleWin($battle_id));
					}
					else {
						debug("Players of battle " . $battle_id . " picked all the same, reset!");
						$newBattle = $this->resetBattle($battle, $picks, $battle_id, true);
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
						$newBattle = $this->resetBattle($battle, $picks, $battle_id, true, true);
						$multiple_winner = true;
					}
					else {
						// One winner
						$winner_id = $winner_array[ $win_key ][0];
						// Notify the user it has won
						User::where('id', $winner_id)->firstOrFail()->notify(new BattleWin($battle->id));
						debug("The winner of battle " . $battle_id . " is user " . $winner_id);
					}
					// Set the pick as a winning in the database
					foreach ($winner_array[ $win_key ] as $winner) {
						$winningPick = Pick::where([['battle_id', $battle_id], ['user_id', $winner]])->firstOrFail();
						$winningPick->has_won = true;
						$winningPick->save();
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

						// Notify the user of the loss
						$user->notify(new BattleLoss(Battle::where('id', $battle_id)->firstOrFail()));

						debug($user->name . " has lost battle " . $battle_id . ", so remove him from the playing users table");
						$user->playingUsers()->delete();
					}
					break;
				// All the possibilities where picked, reset the battle
				case 3:
					debug("Players of battle " . $battle_id . " picked all different, reset!");
					$newBattle = $this->resetBattle($battle, $picks, $battle_id, true);
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
	 * Reset (make new) the battle when it is a draw
	 *
	 * @param $battle
	 * @param $picks
	 * @param $battle_id
	 *
	 * @param bool $sendNotification
	 *
	 * @param bool $is_winner
	 *
	 * @return mixed
	 */
	private function resetBattle ($battle, $picks, $battle_id, $sendNotification = false, $is_winner = false) {
		// Make copy of battle
		$newBattle = $battle->replicate();

		// Set it as a retake
		$newBattle->is_checked_by = NULL;
		$newBattle->is_retake_of = $battle_id;
		$newBattle->save();

		foreach ($picks as $pickBattle) {
			// Re-invite the users
			$user = $pickBattle->user;

			$user->battle_id = $newBattle->decodedId();
			$user->save();
			// Notify the users of the retake
			if ($sendNotification) {
				if ($is_winner) {
					$user->notify(new BattleWin($battle_id, $newBattle->decodedId()));
				}
				else {
					$user->notify(new BattleRetake($newBattle));
				}
			}
		}

		return $newBattle;
	}

	/**
	 * End the round, so remove the players that haven't played
	 * Return true for final battle
	 * Return false for not final battle
	 *
	 * @param Competition $competition
	 *
	 * @return bool
	 */
	public function endRound (Competition $competition) {
		// Fetch last round of competition
		$battleComp = $competition->battle()->orderBy('round', 'DESC')->get();
		$lastBattle = $battleComp->first();
		$round = $lastBattle->round;
		$winner_ids = array_pluck($battleComp->where('round', $round)->toArray(), 'winner_id');
		debug("Last round is " . $round);
		// Disqualify all users which haven't played
		$stillPlayingUsers = $competition->playing_users;
		dump($stillPlayingUsers);
		debug("Checking for players that hasn't played");
		$stillRemainingPlayers = count($stillPlayingUsers);
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
				$this->validate_battle(Battle::where('id', $battle_id)->firstOrFail(), $battle_id, $stillPlayingUser->user);
				$stillRemainingPlayers --;
			}
		}

		if ($stillRemainingPlayers <= 1) {
			// The final battle has been played
			$winner = NULL;
			if ($stillRemainingPlayers == 1) {
				$winner = PlayingUser::where('competition_id', $competition->id)->firstOrFail();
			}
			$this->end_competition($competition, $winner);

			return true;
		}
		else {
			// The final battle hasn't been played yet
			$this->round = $round + 1;
			$this->start_battle($competition);

			return false;
		}
	}

	/**
	 * Get the ranking and outcome of a given battle
	 * The ranking is given including retakes
	 *
	 * @param Battle $battle
	 *
	 * @return array
	 */
	public function getBattleOutcome (Battle $battle) {
		debug("Start with battle outcome");
		// We need to get the first battle of the possible retake
		$firstBattle = $battle;
		$battles = [0 => $battle];
		debug($firstBattle);
		debug("Battle " . $firstBattle->decodedId() . " has retake: " . count($firstBattle->get_retake_of()));
		$notFirstBattle = count($firstBattle->get_retake_of()) > 0;
		while ($notFirstBattle) {
			$firstBattle = $firstBattle->get_retake_of()->first();
			$battles[] = $firstBattle;
			$notFirstBattle = count($firstBattle->get_retake_of()) > 0;
		}

		$ranking = [];
		// Now build the rank from battle one
		for ($battleCount = count($battles) - 1; $battleCount >= 0; $battleCount --) {
			// Get picks of battle
			$picks = $battles[ $battleCount ]->picks();
			$battleRanking = [];
			//dump($battles[ $battleCount ]);
			foreach ($picks as $pick) {
				$battleRanking[] = ["user" => $pick->user->toArray(), "pick" => $pick->toArray(), "win" => false];
			}
			$ranking[] = $battleRanking;
		}

		return $ranking;
	}

	/**
	 * Get the ranking and outcomes of given user
	 *
	 * @param User|NULL $user
	 *
	 * @return array
	 */
	public function getUserOutcome (User $user = NULL) {
		if ($user == NULL) {
			$user = Auth::user();
		}
		$battles = $user->battles;
		$battlesWithRelations = $battles;
		foreach ($battles as $battle) {
			$startBattle = $battle;
			while (count($startBattle->with_retake()) > 0) {
				$startBattle = $startBattle->with_retake()->last();
				$battlesWithRelations->push($startBattle);
			}
		}
		//dump($battlesWithRelations);
		$uniqueBattles = collect([]);
		$battle_ids = [];
		//dump($uniqueBattles);
		foreach ($battlesWithRelations as $battle) {
			if ( ! in_array($battle->id, $battle_ids)) {
				$battle_ids[] = $battle->id;
				$uniqueBattles->push($battle);
			}
		}
		$battles = $uniqueBattles;
		//dump($uniqueBattles);

		$ranking = [];
		$previousCompetitionId = 0;
		$keyComp = - 1;
		foreach ($battles as $battle) {
			if ($battle->competition_id != $previousCompetitionId) {
				$previousCompetitionId = $battle->competition_id;
				$keyComp ++;
			}
			debug("Checking battle " . $battle->decodedId());
			if ($battle->winner_id != NULL) {
				debug("The battle has a winner, so is the last one");
				$ranking[ $keyComp ][] = $this->getBattleOutcome($battle);
			}
		}

		//dd($ranking);

		return $ranking;
	}

	/* ===========================================DEBUG METHODS=========================================== */
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
		ini_set('max_execution_time', 3000);
		ini_set('memory_limit', '-1');
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
}