<?php

namespace Mypleasure\Api\V1\Controller;

use Mypleasure\Api\V1\Controller\CollectionController;
use Mypleasure\Api\V1\Controller\VideoController;
use Mypleasure\Http\Requests\AcquireMediaRequest;
use DB;

class MediaAcquisitionController extends BaseController {

  protected $videoController;

  public function __construct(VideoController $videoController)
  {
    $this->middleware('api.auth');
    $this->videoController = $videoController;
  }

  /**
   * Fetch new videos ready to be displyed.
   * As a convenience, return the number of videos
   * currently pending process.
   *
   * @param  integer|mixed $userId
   * @return Response
   */
  public function fetch($userId)
  {
    $newAndReady = $this->fetchNewAndReady($userId);
    $pending = $this->getPendingNumber($userId);
    $message = '';
    $status_code = 200;

    switch ($newAndReady) {
      case null:
        $message = 'No new videos.';
        break;

      case true:
        $message = 'New videos added.';
        break;

      case false:
        $message = 'Error while creating video instance.';
        $status_code = 500;
        break;
    }

    return response()->json([
      'status_code' => $status_code,
      'message' => $message,
      'pending' => (int) $pending
    ], $status_code);
  }

  /**
   * Queue proposed video URL into video store, so that
   * it gets processed later on and hopefully added to
   * the user's collection.
   *
   * @param  AcquireMediaRequest  $request
   * @param  CollectionController $collectionController
   * @return Response
   */
  public function acquire(AcquireMediaRequest $request, CollectionController $collectionController)
  {
    $user = \JWTAuth::parseToken()->toUser();

    // Is request coming from TARS (browser add-on)?
    $fromTars = $request->input('tars', false);

    // URL to process.
    $url = $request->input('url');

    // Get the collection ID to add the video to.
    // If value is null, it means User
    // wants to create a new collection.
    $collectionId = trim($request->input('collection', null));
    if (is_null($collectionId)) {
      $collectionName = $request->input('name', null);
    } else {
      $collectionId = (int) $collectionId;
    }

    if ($collectionName) {
      $collectionController->createCollection($collectionName, false, $user->id);
    }
  }

  protected function getPendingNumber($userId)
  {
    return \DB::table('mediaqueue')
      ->where('status', 'pending')
      ->where('requester', (int) $userId)
      ->count();
  }

  /**
   * Find in video store all videos belonging to user
   * that are ready to be used.
   * For each of them, use data to create a Video instance.
   *
   * @param  integer|mixed $userId
   * @return boolean|null  True if new videos, null if none, false if error.
   */
  protected function fetchNewAndReady($userId)
  {
    $videosReady = \DB::table('mediaqueue')
      ->where('status', 'ready')
      ->where('requester', (int) $userId)
      ->get();

    if (count($videosReady) === 0) return null;

    foreach ($videosReady as $video) {
      $data = \DB::table('mediastore')
        ->where('hash', $video->hash)
        ->first();

      $collectionId = $video->collection_id;

      $created = $this->createVideoInstance($collectionId, $data);
      if (!(bool)$created) return false;

      // Mark as 'done'.
      \DB::table('mediaqueue')
        ->where('status', 'ready')
        ->where('requester', $userId)
        ->update(array('status' => 'done'));
    }

    return true;
  }

  protected function createVideoInstance($collectionId, $data)
  {
    return $this->videoController->createVideo(
      $data->hash, $data->title, $collectionId, $data->original_url,
      $data->embed_url, $data->duration, $data->poster, $data->naughty
    );
  }

  protected function retrieveVideoInStoreFromHash($hash)
  {
    $video = \DB::table('mediastore')
      ->where('hash', $hash)
      ->get();

    return $video;
  }

}