<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToUsersRoles extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('users') && Schema::hasTable('roles')) {
      if (Schema::hasColumn('users', 'role_id')) {
        Schema::table('users', function (Blueprint $table) {
          $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
      }
    }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('users')) {
      if (Schema::hasColumn('users', 'role_id')) {
        Schema::table('users', function(Blueprint $table) {
          $table->dropForeign('users_role_id_foreign');
        });
      }
    }
	}

}
