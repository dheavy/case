<?php

use Way\Tests\Should;
use Mypleasure\User;
use Mypleasure\Role;

class UserTest extends TestCase {

  public function setUp()
  {
    parent::setUp();
    $this->prepareTestDB();
    $this->createApplication();
  }

  public function testUserIsCreatedWithDummyEmailByDefault()
  {
    $user = User::create([
      'username' => 'morgane',
      'password' => 'azertyuiop'
    ]);

    Should::contain(User::$EMAIL_PLACEHOLDER_SUFFIX, $user->email);
  }

  public function testUserIsCuratorByDefault()
  {
    $user = User::create([
      'username' => 'marion',
      'password' => 'azertyuiop'
    ]);

    $curator = Role::where('name', '=', 'curator')->first();

    Should::beEquals($user->role->id, $curator->id);
  }

  public function testUserRoleNotAlteredDuringSave()
  {
    $user = User::create([
      'username' => 'marion',
      'password' => 'azertyuiop'
    ]);

    $curator = Role::where('name', '=', 'curator')->first();
    $admin = Role::where('name', '=', 'admin')->first();

    $user->email = 'marion@mypleasu.re';
    $user->save();

    Should::beEquals($user->role->id, $curator->id);

    $user->role()->associate($admin);
    Should::beEquals($user->role->id, $admin->id);

    $user->email = 'marioune@mypleasu.re';
    $user->save();

    Should::beEquals($user->role->id, $admin->id);
  }

  public function testPromoteToAdmin()
  {
    $user = User::create([
      'username' => 'morgane',
      'password' => 'azertyuiop'
    ]);

    $curator = Role::where('name', '=', 'curator')->first();
    $admin = Role::where('name', '=', 'admin')->first();

    Should::beEquals($user->role->id, $curator->id);

    Should::beTrue($user->promote());

    Should::beEquals($user->role->id, $admin->id);
  }

  public function testDemoteToCurator()
  {
    $user = User::create([
      'username' => 'morgane',
      'password' => 'azertyuiop'
    ]);

    $curator = Role::where('name', '=', 'curator')->first();

    $user->promote();

    Should::beTrue($user->demote());

    Should::beEquals($user->role->id, $curator->id);
  }

  public function testHasPlaceholderEmail()
  {
    $user = User::create([
      'username' => 'marion',
      'password' => 'azertyuiop'
    ]);

    Should::beTrue($user->hasPlaceholderEmail());

    $user->email = 'marion@mypleasu.re';
    $user->save();

    Should::beFalse($user->hasPlaceholderEmail());
  }

}