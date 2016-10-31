<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWinnerIdToBattle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('battles', function (Blueprint $table) {
            $table->integer("winner_id")->unsigned()->nullable()->after("round");
	        $table->foreign('winner_id')
	              ->references('id')
	              ->on('users')
	              ->onDelete('restrict')
	              ->onUpdate('cascade');
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
	        $table->dropForeign(["winner_id"]);
            $table->dropColumn("winner_id");
        });
    }
}
