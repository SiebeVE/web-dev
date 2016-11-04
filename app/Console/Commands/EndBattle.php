<?php

namespace App\Console\Commands;

use App\BattleLogic\BattleLogic;
use App\Competition;
use Illuminate\Console\Command;

class EndBattle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'battle:end {competition : The id of a competition}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'End a round of a given competition';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	    $competitionId = $this->argument('competitionId');
	    $competition = Competition::where('id', $competitionId)->firstOrFail();
        $battle = new BattleLogic();
	    $battle->endRound($competition);
    }
}
