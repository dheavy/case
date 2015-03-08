<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNsfwToVideos extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('videos')) {
			Schema::table('videos', function (Blueprint $table) {
				$table->integer('nsfw')->nullable();
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
			Schema::table('videos', function (Blueprint $table) {
				$table->dropColumn('nsfw');
			});
		}
	}

}
