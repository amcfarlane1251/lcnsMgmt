<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnitToLicenseseatTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('license_seats', function(Blueprint $table)
		{
			$table->integer('unit_id')->unsigned()->index();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('license_seats', function(Blueprint $table)
		{
			$table->dropColumn('unit_id');
		});
	}

}
