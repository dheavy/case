<?php

use Way\Tests\Should;
use Mypleasure\Collection;
use Mypleasure\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CollectionTest extends TestCase {

  use DatabaseTransactions;

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

  public function testIsPublicByDefault()
  {
    $collection = Collection::create([
      'name' => 'collection'
    ]);

    Should::beFalse($collection->private);
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