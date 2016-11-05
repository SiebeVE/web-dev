<?php

namespace App\Console\Commands;

use App\BattleLogic\BattleLogic;
use App\Competition;
use App\GameSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckCompetition extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'check:competition';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check if a competition subscribe period has ended';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct () {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle () {
		$gameSettings = new GameSetting();
		$battleLogic = new BattleLogic();
		// Fetch all competitions
		$competitions = Competition::get();

		// create a competition if there are none
		if (count($competitions) == 0) {
			$this->info("No competition has started, so lets make one.");
			$competition = $battleLogic->start_competition();
		}

		foreach ($competitions as $competition) {
			$timeNowCarbon = Carbon::now();
			//$timeNowCarbon = Carbon::now()->addDays(7); // End subscription
			//$timeNowCarbon = Carbon::now()->addHours(24); // End round
			//$timeNowCarbon = Carbon::now()->addMonths(4); // End period

			// Check if the battle round has ended and the competition doesn't have winner yet
			$debugRound = $gameSettings->getData('endRoundForCompetition') == $competition->id && env('APP_DEBUG', false);
			$hasRoundEnd = $competition->battle_start_date != NULL && floor($competition->battle_start_date->diffInHours($timeNowCarbon)) >= intval($gameSettings->getData('lengthOfBattle')) && !$competition->has_finished;
			$this->info("The debug for the round is " . ($debugRound ? "true" : "false"));
			debug("DEBUG CONSOLE: The debug for the round is " . ($debugRound ? "true" : "false"));
			$this->info("The normal for the round is " . ($hasRoundEnd ? "true" : "false"));
			debug("DEBUG CONSOLE: The normal for the round is " . ($hasRoundEnd ? "true" : "false"));
			if ($hasRoundEnd || ($debugRound)) {
				// End the round
				$result = $battleLogic->endRound($competition);
				$this->info("The result of the end round is " . ($result ? "true" : "false"));
				debug("DEBUG CONSOLE: The result of the end round is " . ($result ? "true" : "false"));
				if ($result) {
					// If the final round has been played, start a new competition
					$battleLogic->start_competition();
					if ($debugRound) {
						$gameSettings->setData('endRoundForCompetition', '');
					}
				}
			}

			// Check if the competition unsubscribe period has ended and if the battles haven't started yet
			$debugSubscribe = $gameSettings->getData('endSubscribeForCompetition') == $competition->id && env('APP_DEBUG', false);
			$hasSubscribeEnd = floor($competition->start_date->diffInDays($timeNowCarbon)) >= intval($gameSettings->getData('lengthOfUnsubscribePeriod')) && $competition->battle_start_date == NULL;
			$this->info("The debug for the subscribe period is " . ($debugRound ? "true" : "false"));
			debug("DEBUG CONSOLE: The debug for the subscribe period is " . ($debugRound ? "true" : "false"));
			$this->info("The normal for the subscribe period is " . ($hasSubscribeEnd ? "true" : "false"));
			debug("DEBUG CONSOLE: The normal for the subscribe period is " . ($hasSubscribeEnd ? "true" : "false"));
			if ($hasSubscribeEnd || ($debugSubscribe)) {
				// Start the competition
				$battleLogic->start_battle($competition);
				if ($debugSubscribe) {
					$gameSettings->setData('endSubscribeForCompetition', '');
				}
			}
		}
	}
}
