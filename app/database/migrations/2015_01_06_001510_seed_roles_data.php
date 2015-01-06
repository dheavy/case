<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Seed Roles with required data.
 *
 * This is not some test content, it is actual, fundamental
 * data needed for roles to be active. It might also benefit
 * from migrations, if we ever want to update it.
 * Two reasons why we use migration instead of seeding.
 */
class SeedRolesData extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (Schema::hasTable('roles')) {
      $models = array(
        array('name' => 'admin', 'created_at' => new DateTime(), 'updated_at' => new DateTime()),
        array('name' => 'curator', 'created_at' => new DateTime(), 'updated_at' => new DateTime())
      );

      DB::table('roles')->insert($models);
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    if (Schema::hasTable('roles')) {
      DB::table('roles')->truncate();
    }
  }

}
