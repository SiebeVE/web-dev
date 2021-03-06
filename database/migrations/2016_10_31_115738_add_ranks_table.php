<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->increments('id');

	        $table->integer("user_id")->unsigned();
	        $table->foreign("user_id")
		        ->references("id")
		        ->on("users")
		        ->onDelete("cascade")
		        ->onUpdate("cascade");

	        $table->integer("competition_id")->unsigned();
	        $table->foreign("competition_id")
		        ->references("id")
		        ->on("competitions")
		        ->onDelete("cascade")
		        ->onUpdate("cascade");

	        $table->integer("rank")->unsigned();

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
        Schema::dropIfExists('ranks');
    }
}
