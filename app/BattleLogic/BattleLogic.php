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
use App\PlayingUser;
use App\User;
use Carbon\Carbon;

/**
 * This class controls my game
 *
 * Class BattleLogic
 * @package App\BattleLogic
 */
class BattleLogic
{
	protected $currentPeriodStart;
	protected $numberOfPlayers;
	protected $lengthOfBattle;
	protected $round;

	public function __construct () {
		// Fetch the current period start date
		$this->currentPeriodStart = Carbon::now();
		$this->numberOfPlayers = 2;
		$this->lengthOfBattle = 24;
		$this->round = 1;
	}

	/**
	 * Prepare the database for a new competition
	 *
	 * @return bool
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

		return true;
	}

	/**
	 * Initialize all the battles for the currently playing users
	 *
	 * @return bool
	 */
	public function start_battle () {
		// Get all the playing users
		$playingUsers = PlayingUser::with("user")->inRandomOrder()->get();

		// Check if their is a user single
		if (count($playingUsers) % $this->numberOfPlayers == 1) {
			debug("there is a single user, create battle with one extra");
			// Create a battle with one more user then intended
			$playingUserArray = [];

			for ($counter = $this->numberOfPlayers + 1; $counter != 0; $counter --) {
				$playingUserArray[] = $playingUsers->last();
				$playingUsers->pop();
			}
			$this->create_battle($playingUserArray, $this->lengthOfBattle, $this->round);
		}

		debug("Their are " . count($playingUsers) . " not yet in a battle.");

		$playingUserArray = [];
		do {
			// Populate the array with the last user
			$playingUserArray[] = $playingUsers->last();
			$playingUsers->pop();
			// Check if the next battle has to be created
			if (count($playingUserArray) == $this->numberOfPlayers) {
				// The array is full, the same number as the number of players
				debug("The array is full, create a new battle");
				$this->create_battle($playingUserArray, $this->lengthOfBattle, $this->round);
				// Empty it out for the next one
				$playingUserArray = [];
			}
			debug("Their are " . count($playingUsers) . " not yet in a battle.");
		} while ($playingUsers->last() != NULL);

		if(count($playingUserArray) != 0)
		{
			// When their are still users which aren't in a battle
			debug("Create final battle with last users");
			$this->create_battle($playingUserArray, $this->lengthOfBattle, $this->round);
		}

		debug("Finished");
		return true;
	}

	/**
	 * Create a battle and set the proper columns for the user
	 *
	 * @param $playingUsers
	 * @param $lengthBattleInHours
	 * @param $round
	 *
	 * @return Battle
	 */
	private function create_battle ($playingUsers, $lengthBattleInHours, $round) {
		// Battle starts right now
		$start_date = Carbon::now();
		// Create the battle
		$newBattle = Battle::create([
			"start_date" => $start_date,
			"end_date"   => $start_date->addHour($lengthBattleInHours),
			"round"      => $round
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
}