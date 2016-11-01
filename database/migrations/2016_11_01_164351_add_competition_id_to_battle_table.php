<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompetitionIdToBattleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('battles', function (Blueprint $table) {
	        $table->integer("competition_id")->unsigned()->after("end_date");
	        $table->foreign("competition_id")
	              ->references("id")
	              ->on("competitions")
	              ->onDelete("cascade")
	              ->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('battles', function (Blueprint $table) {
	        $table->dropForeign(['competition_id']);
            $table->dropColumn('competition_id');
        });
    }
}
