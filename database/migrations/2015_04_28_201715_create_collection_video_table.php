<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCollectionVideoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('collection_video', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('collection_id')->unsigned()->index();
			$table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
			$table->integer('video_id')->unsigned()->index();
			$table->foreign('video_id')->references('id')->on('videos')->onDelete('cascade');
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
		Schema::drop('collection_video');
	}

}
