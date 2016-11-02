<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsCheckedByColumnToBattle extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up () {
		Schema::table('battles', function (Blueprint $table) {
			$table->integer('is_checked_by')->unsigned()->nullable()->after("round");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down () {
		Schema::table('battles', function (Blueprint $table) {
			$table->dropColumn('is_checked_by');
		});
	}
}
