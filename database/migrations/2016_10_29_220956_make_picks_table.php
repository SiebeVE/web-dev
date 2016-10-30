<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakePicksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('picks', function (Blueprint $table) {
            $table->increments('id');
	        $table->integer("battle_id")->unsigned();
	        $table->integer("user_id")->unsigned();
	        $table->string("pick");
	        $table->string("ip_address");
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
        Schema::dropIfExists('picks');
    }
}
