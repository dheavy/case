<?php

use Way\Tests\Should;
use Mypleasure\User;
use Mypleasure\Video;
use Mypleasure\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase {

  use DatabaseTransactions;

  public function testUserIsCreatedWithDummyEmailByDefault()
  {
    $user = User::create([
      'username' => 'morgane',
      'password' => 'azertyuiop'
    ]);

    Should::contain(User::$EMAIL_PLACEHOLDER_SUFFIX, $user->email);
  }

  public function testNotAdminBydefault()
  {
    $user = User::create([
      'username' => 'marion',
      'password' => 'azertyuiop'
    ]);

    Should::beFalse($user->admin);
  }

  public function testPromoteToAdmin()
  {
    $user = User::create([
      'username' => 'morgane',
      'password' => 'azertyuiop'
    ]);

    Should::beFalse($user->admin);
    Should::beTrue($user->promote());
    Should::beTrue($user->admin);
  }

  public function testDemoteFromAdmin()
  {
    $user = User::create([
      'username' => 'morgane',
      'password' => 'azertyuiop'
    ]);

    $user->promote();
    Should::beTrue($user->demote());
    Should::beFalse($user->admin);
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

  public function testHasVideosManyThroughCollections()
  {
    $user = User::create([
      'username' => 'marion',
      'password' => 'azertyuiop'
    ]);

    $collection = Collection::create([
      'name' => 'collection'
    ]);

    $video0 = Video::create([
      'hash' => 'azertyuiop0',
      'title' => 'video0',
      'poster' => 'poster.jpg',
      'original_url' => 'http://original.url',
      'embed_url' => 'http://embed.url',
      'duration' => '00:11:22',
      'naughty' => false
    ]);

    $video1 = Video::create([
      'hash' => 'azertyuiop1',
      'title' => 'video1',
      'poster' => 'poster.jpg',
      'original_url' => 'http://original.url',
      'embed_url' => 'http://embed.url',
      'duration' => '00:11:22',
      'naughty' => false
    ]);

    $video2 = Video::create([
      'hash' => 'azertyuiop2',
      'title' => 'video2',
      'poster' => 'poster.jpg',
      'original_url' => 'http://original.url',
      'embed_url' => 'http://embed.url',
      'duration' => '00:11:22',
      'naughty' => false
    ]);

    $user->collections()->first()->videos()->save($video0);

    $collection->videos()->saveMany([$video1, $video2]);
    $collection->save();

    $user->collections()->save($collection);
    $user->save();

    Should::equal($user->videos->count(), 3);
  }

}