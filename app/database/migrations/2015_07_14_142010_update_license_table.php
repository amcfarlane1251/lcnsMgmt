<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateLicenseTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('licenses', function(Blueprint $table)
		{
			$table->integer('type_id')->unsigned()->nullable();
			$table->integer('role_id')->unsigned()->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('licenses', function(Blueprint $table)
		{
			//
		});
	}

}
