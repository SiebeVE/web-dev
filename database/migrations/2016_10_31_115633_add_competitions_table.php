<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompetitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->increments('id');
	        $table->dateTime("start_date");

	        $table->integer("winner_id")->unsigned()->nullable();
	        $table->foreign("winner_id")
		        ->references("id")
		        ->on("users")
		        ->onDelete("restrict")
		        ->onUpdate("cascade");

	        $table->dateTime("period_start");
	        $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('competitions');
    }
}
