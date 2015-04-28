<?php

use Way\Tests\Should;
use Mypleasure\User;

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

    $curator = \Mypleasure\Role::where('name', '=', 'curator')->first();

    Should::beEquals($user->role->id, $curator->id);
  }

}