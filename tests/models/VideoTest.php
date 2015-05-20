<?php

use Mypleasure\Video;
use Mypleasure\Collection;

use Way\Tests\Should;

class VideoTest extends TestCase {

  public function setUp()
  {
    parent::setUp();
    $this->prepareTestDB();
    $this->createApplication();
  }

  public function testSavedWithSlug()
  {
    $video = Video::create([
      'hash' => 'azertyuiop',
      'title' => 'mY viDeo',
      'poster' => 'poster.jpg',
      'original_url' => 'http://original.url',
      'embed_url' => 'http://embed.url',
      'duration' => '00:11:22',
      'naughty' => false
    ]);

    Should::beEquals($video->slug, 'my-video');
  }

  public function testIsPublic()
  {
    $video = Video::create([
      'hash' => 'azertyuiop',
      'title' => 'my video',
      'poster' => 'poster.jpg',
      'original_url' => 'http://original.url',
      'embed_url' => 'http://embed.url',
      'duration' => '00:11:22',
      'naughty' => false
    ]);

    $collection = Collection::create([
      'name' => 'collection'
    ]);

    $collection->save();

    $video->collection()->associate($collection)->save();

    Should::beEquals($collection->videos()->count(), 1);
    Should::beFalse($collection->private);
    Should::beFalse($video->isPrivate());

    $collection->private = true;
    $collection->save();
    Should::beTrue($collection->private);
    Should::beTrue($video->isPrivate());
  }

}