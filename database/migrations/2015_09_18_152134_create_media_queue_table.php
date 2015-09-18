<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaQueueTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('mediaqueue', function (Blueprint $table) {
      $table->increments('id');
      $table->string('hash')->index();
      $table->string('url');
      $table->integer('requester');
      $table->integer('collection_id');
      $table->string('status');
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
    Schema::drop('mediaqueue');
  }
}
