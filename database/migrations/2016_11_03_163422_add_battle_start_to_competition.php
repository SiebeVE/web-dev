<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBattleStartToCompetition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('competitions', function (Blueprint $table) {
		    $table->dateTime("battle_start_date")->nullable()->after('start_date');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('competitions', function (Blueprint $table) {
		    $table->removeColumn("battle_start_date");
	    });
    }
}
