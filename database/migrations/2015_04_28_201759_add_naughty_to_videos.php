<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNaughtyToVideos extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('videos')) {
			Schema::table('videos', function (Blueprint $table)
			{
				$table->integer('naughty')->default(0);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('videos')) {
			Schema::table('videos', function (Blueprint $table)
			{
				$table->dropColumn('naughty');
			});
		}
	}

}
