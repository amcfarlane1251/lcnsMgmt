<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('roles', function(Blueprint $table)
		{
			$table->dropColumn('user_id');
		});

		Schema::create('roles_users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('role_id')->unsigned();
			$table->integer('user_id')->unsigned();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('roles', function(Blueprint $table)
		{
			//
		});
	}

}
