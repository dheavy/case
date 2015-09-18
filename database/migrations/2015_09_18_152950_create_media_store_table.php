<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mediastore', function (Blueprint $table) {
        $table->increments('id');
        $table->string('hash')->index();
        $table->string('title');
        $table->string('original_url');
        $table->string('embed_url');
        $table->string('poster');
        $table->string('duration');
        $table->boolean('naughty');
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
      Schema::drop('mediastore');
    }
}
