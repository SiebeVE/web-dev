<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelationsPicks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('picks', function (Blueprint $table) {
	        $table->foreign('user_id')
	              ->references('id')
		          ->on('users')
	              ->onDelete('restrict')
	              ->onUpdate('cascade');

	        $table->foreign('battle_id')
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
        Schema::table('picks', function (Blueprint $table) {
	        $table->dropForeign(['user_id']);
	        $table->dropForeign(['battle_id']);
        });
    }
}
