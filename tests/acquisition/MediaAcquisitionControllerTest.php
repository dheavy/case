<?php

use Way\Tests\Should;
use Mypleasure\User;

class MediaAcquisitionControllerTest extends TestCase {

  protected $token;
  protected $user;

  public function __construct()
  {
    parent::setUp();
    $this->token = $this->getToken();
    $this->user = User::where('username', 'davy')->first();
  }

  public function testFetch()
  {
    $newAndReady = \DB::table('mediaqueue')
      ->where('status', 'ready')
      ->where('requester', (int) $this->user->id)
      ->count();

    $pending = \DB::table('mediaqueue')
      ->where('status', 'pending')
      ->where('requester', (int) $this->user->id)
      ->count();

    $this->get(
        '/api/media/fetch/' . $this->user->id,
        ['Authorization' => 'Bearer ' . $this->token]
      )
      ->seeJson([
        'pending' => (int) $pending,
        'message' => 'New videos added.',
        'status_code' => 200
      ]);

    \DB::table('mediaqueue')
      ->where('status', 'ready')
      ->update(['status' => 'pending']);

    $refreshPending = \DB::table('mediaqueue')
      ->where('status', 'pending')
      ->where('requester', (int) $this->user->id)
      ->count();

    $this->get(
        '/api/media/fetch/' . $this->user->id,
        ['Authorization' => 'Bearer ' . $this->token]
      )->seeJson([
        'pending' => (int) $refreshPending,
        'message' => 'No new videos.',
        'status_code' => 200
      ]);
  }

  public function testAcquireNewInExistingCollection()
  {
    $auth = ['Authorization' => 'Bearer ' . $this->getToken()];
    $data = [
      'url' => 'https://www.youtube.com/watch?v=7WRFUXyVZoQ',
      'collection' => $this->user->collections->first()->id
    ];

    \DB::table('mediaqueue')->truncate();
    \DB::table('mediastore')->truncate();

    $response = $this->call('POST', '/api/media/acquire', $data, $auth);

    Should::equal(201, $response->status());
  }

}