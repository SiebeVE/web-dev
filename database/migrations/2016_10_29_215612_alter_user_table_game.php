<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserTableGame extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
	        $table->renameColumn('token', 'token_mail');
	        $table->string("choice_paper")->nullable()->after("is_admin");
	        $table->integer("battle_id")->unsigned()->after("choice_paper");
	        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
	        $table->renameColumn('token_mail', 'token');
	        $table->dropColumn([
	        	"choice_paper",
		        "battle_id"
	        ]);
	        $table->dropSoftDeletes();
        });
    }
}
