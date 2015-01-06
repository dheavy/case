<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users', function(Blueprint $table)
    {
      $table->increments('id');
      $table->string('username', 64)->unique();
      $table->string('password', 100);
      $table->string('email', 128)->unique();
      $table->integer('role_id')->unsigned()->index()->nullable();
      $table->string('status', 50)->nullable();
      $table->rememberToken();
      $table->timestamps();
      $table->softDeletes();
    });
  }


  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('users');
  }

}
