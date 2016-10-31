<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsRetakeOfToBattle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('battles', function (Blueprint $table) {
            $table->integer("is_retake_of")->unsigned()->nullable()->after("winner_id");
	        $table->foreign('is_retake_of')
	              ->references('id')
	              ->on('battles')
	              ->onDelete('cascade')
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
	        $table->dropForeign(["is_retake_of"]);
            $table->dropColumn("is_retake_of");
        });
    }
}
