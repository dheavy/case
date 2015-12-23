<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('videos', function(Blueprint $table) {
			$table->increments('id');
			$table->string('hash')->index();
			$table->integer('collection_id')->nullable()->index();
			$table->string('title')->nullable();
			$table->string('slug')->nullable();
			$table->string('poster')->nullable();
			$table->string('original_url');
			$table->string('embed_url');
			$table->string('duration')->nullable();
			$table->boolean('naughty')->default(false);
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
		Schema::dropIfExists('videos');
	}

}
