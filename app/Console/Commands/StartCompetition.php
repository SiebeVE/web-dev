<?php

namespace App\Console\Commands;

use App\BattleLogic\BattleLogic;
use Illuminate\Console\Command;

class StartCompetition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'competition:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a new competition';

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
        $battle = new BattleLogic();
	    $battle->start_competition();
    }
}
