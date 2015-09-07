<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvitesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invites', function(Blueprint $table)
		{
			$table->string('email')->index();
			$table->string('code')->index();
			$table->integer('from_id')->references('id')->on('users');
			$table->string('message')->nullable();
			$table->timestamp('claimed_at')->nullable();
			$table->timestamp('created_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('invites');
	}

}
