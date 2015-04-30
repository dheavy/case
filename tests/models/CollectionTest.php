<?php

use Way\Tests\Should;
use Mypleasure\Collection;
use Mypleasure\User;

class CollectionTest extends TestCase {

  public function setUp()
  {
    parent::setUp();
    $this->prepareTestDB();
    $this->createApplication();
  }

  public function testSavedWithSlug()
  {
    $collection = Collection::create([
      'name' => 'mY sWeet collection'
    ]);

    Should::beEquals($collection->slug, 'my-sweet-collection');
  }

  public function testSaveDefaultCollectionOnNewUser()
  {
    $user = User::create([
      'username' => 'marion',
      'password' => 'azertyuiop'
    ]);

    Should::beEquals($user->collections()->count(), 1);
    Should::beEquals($user->collections()->first()->name, $user->username);
  }

  public function testIsPublic()
  {
    $collection = Collection::create([
      'name' => 'collection'
    ]);

    Should::beEquals($collection->isPublic(), 1);
  }

  public function testIsDefault()
  {
    $user = User::create([
      'username' => 'marion',
      'password' => 'azertyuiop'
    ]);

    Should::beTrue($user->collections()->first()->isDefault());
  }

}