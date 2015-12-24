<?php

use Way\Tests\Should;
use Mypleasure\User;

class MediaAcquisitionControllerTest extends TestCase {

  protected $token;
  protected $user;

  public function setUp()
  {
    parent::setUp();
    $this->artisan('db:seed', ['--env' => 'testing']);
    $this->user = User::where('username', 'davy')->first();

    // Because signing in will make each test trigger more than one request,
    // something is not being flushed internally and messes up with auth...
    // So you have to use `refreshApplication`.
    // @see https://laracasts.com/discuss/channels/testing/laravel-testcase-not-sending-authorization-headers
    $this->signIn();
    $this->refreshApplication();
  }

  public function testCanFetchMediaFromQueueAndStoreWhenAuthenticated()
  {
    $fetch1 = $this->fetch1($this->token);
    $result = $fetch1['result'];
    $pending = $fetch1['pending'];
    $refreshPending = $this->fetch2()['refreshPending'];

    Should::equal(200, $result->status_code);
    Should::equal('New videos added.', $result->message);
    Should::equal($pending, $result->pending);

    $fetch2 = $this->fetch2($this->token);
    $result = $fetch2['result'];
    $refreshPending = $fetch2['refreshPending'];

    Should::equal(200, $result->status_code);
    Should::contain('No new videos', $result->message);
    Should::equal($refreshPending, $result->pending);
  }

  public function testCanNotFetchMediaFromQueueAndStoreWhenUnauthenticated()
  {
    $fetch = $this->fetch1();
    $result = $fetch['result'];
    $pending = $fetch['pending'];
    $newAndReady = $fetch['newAndReady'];

    Should::equal(401, $result->status_code);

    $fetch2 = $this->fetch2();
    $result = $fetch2['result'];
    $refreshPending = $fetch2['refreshPending'];

    Should::equal(401, $result->status_code);
  }

  public function testCanAcquireNewMediaInExistingCollectionWhenAuthenticated()
  {
    // Create POST data.
    $data = $this->createPostData(['collectionId' => $this->user->collections->first()->id]);

    // Add a new video to Davy's first collection.
    $this->post(
      '/api/media/acquire',
      $data,
      ['Authorization' => 'Bearer ' . $this->token]
    )->seeJson([
      'status_code' => 201,
      'message' => 'Requested video was added to media queue for processing.'
    ]);
  }

  public function testCanNotAcquireNewMediaInExistingCollectionWhenUnauthenticated()
  {
    // Create POST data.
    $data = $this->createPostData(['collectionId' => $this->user->collections->first()->id]);

    // Add a new video to Davy's first collection.
    $this->post(
      '/api/media/acquire',
      $data
    )->seeJson([
      'status_code' => 401
    ]);
  }

  public function testCanAcquireNewMediaInNewCollectionWhenAuthenticated()
  {
    $collectionName = 'my collection';

    $data = $this->createPostData(['name' => $collectionName]);

    $this->post(
      '/api/media/acquire',
      $data,
      ['Authorization' => 'Bearer ' . $this->token]
    )->seeJson([
      'status_code' => 201,
      'message' => 'Requested video was added to media queue for processing.'
    ]);

    Should::equal($collectionName, $this->user->collections->last()->name);
  }

  public function testCanNotAcquireNewMediaInNewCollectionWhenUnauthenticated()
  {
    $collectionName = 'my collection';

    $data = $this->createPostData(['name' => $collectionName]);

    $this->post(
      '/api/media/acquire',
      $data
    )->seeJson([
      'status_code' => 401,
    ]);

    Should::notEqual($collectionName, $this->user->collections->last()->name);
  }

  protected function fetch1($token = null)
  {
    // Prepare comparable values based on seeding.
    // After seeding there are in media queue:
    // - 2 "ready"
    // - 2 "pending"
    // - 1 "done"
    $newAndReady = \DB::table('mediaqueue')
      ->where('status', 'ready')
      ->where('requester', (int) $this->user->id)
      ->count(); // -> 2

    $pending = \DB::table('mediaqueue')
      ->where('status', 'pending')
      ->where('requester', (int) $this->user->id)
      ->count(); // -> 2

    // Fetch once.
    $response = $this->call(
      'GET',
      '/api/media/fetch/' . $this->user->id,
      ['token' => $token]
    );

    return [
      'result' => json_decode($response->content()),
      'pending' => $pending,
      'newAndReady' => $newAndReady
    ];
  }

  protected function fetch2($token = null)
  {
    // Change all 'ready' to 'pending'.
    \DB::table('mediaqueue')
      ->where('status', 'ready')
      ->update(['status' => 'pending']); // -> 0 "ready", 4 "pending"

    // Get new comparable values.
    $refreshPending = \DB::table('mediaqueue')
      ->where('status', 'pending')
      ->where('requester', (int) $this->user->id)
      ->count(); // -> 4

    $response = $this->call(
      'GET',
      '/api/media/fetch/' . $this->user->id,
      ['token' => $token]
    );

    return [
      'result' => json_decode($response->content()),
      'refreshPending' => $refreshPending
    ];
  }

  protected function createPostData($args = [])
  {
    $data = ['url' => 'https://www.youtube.com/watch?v=7WRFUXyVZoQ'];

    if (array_key_exists('name', $args)) {
      $data['name'] = $args['name'];
    } elseif (array_key_exists('collectionId', $args)) {
      $data['collection_id'] = $args['collectionId'];
    }

    return $data;
  }

}
