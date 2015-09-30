<?php

namespace Mypleasure\Api\V1\Controller;

use Mypleasure\Api\V1\Controller\CollectionController;
use Mypleasure\Api\V1\Controller\VideoController;
use Mypleasure\Http\Requests\AcquireMediaRequest;
use Carbon\Carbon;

class MediaAcquisitionController extends BaseController {

  protected $videoController;
  protected $collectionController;
  protected $userController;

  public function __construct(VideoController $videoController,
                              CollectionController $collectionController,
                              UserController $userController)
  {
    $this->middleware('api.auth', ['expect' => 'acquire']);
    $this->videoController = $videoController;
    $this->collectionController = $collectionController;
    $this->userController = $userController;
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
   * Deal with user's request to add a video to its collections.
   *
   * The body of the request contains either (by order to precedence):
   *
   * - collection_id: the ID of the collection owned by current user
   *                  where she'd like to place the video,
   *
   * - name:          the name of a new collection to create and store
   *                  the new video at.
   *
   * After parsing the request, if it succeeds, this method will either:
   *
   * - store to media queue the requested video with related data,
   *   to be processed by TARS,
   *
   * - create directly an instance of the video if matching data is
   *   to be found in the store (i.e. when the same video has already
   *   been scraped before in a previous request).
   *
   * @param  AcquireMediaRequest  $request
   * @return Response
   */
  public function acquire(AcquireMediaRequest $request)
  {
    $user = \JWTAuth::parseToken()->toUser();
    $collectionName = null;

    // URI to process, and resulting hash.
    $url = $this->normalizeUrl($request->input('url'));
    $hash = md5(urlencode(utf8_encode($url)));

    // Prevent from making a duplicate of an already collected video.
    if ($this->userController->hasVideoMatchingHash($hash, 'pending')) {
      return response()->json([
        'status_code' => 205,
        'message' => 'Video was already added to queue.'
      ], 205);
    }

    // Get the collection ID to add the video to.
    // If value is null, it means User
    // wants to create a new collection.
    $collectionId = $request->input('collection_id', null);
    if (is_null($collectionId)) {
      $collectionName = $request->input('name', null);
    } else {
      $collectionId = (int) $collectionId;

      // Ensure the collection ID referes to one of user's.
      if (!$this->userController->ownsCollection($user->id, $collectionId)) {
        return response()->json([
          'status_code' => 422,
          'message' => 'Collection ID is invalid.'
        ], 422);
      }
    }

    // Now... create the new collection if a name was passed.
    // Collection ID becomes the ID of the newly created collection.
    if ($collectionName) {
      $newCollection = $this->collectionController->createCollection($collectionName, false, $user->id);
      $collectionId = $newCollection->id;
    }

    // No collection ID nor new collection name? Error.
    if (!$collectionId && !$collectionName) {
      return response()->json([
        'status_code' => 422,
        'message' => 'Missing existing collection ID or new collection name.'
      ], 422);
    }

    // Check if video was already processed before, i.e. already in the media store.
    // Create a Video instance directly if it's the case.
    $video = $this->retrieveVideoInStoreFromHash($hash);
    if ((bool) $video) {
      $this->createVideoInstance($collectionId, $video[0]);
      return response()->json([
        'status_code' => 201,
        'message' => 'Video instance was created from a previous occurence in the media store.'
      ], 201);
    }

    // No previous occurence of this video
    // was found in the media store,
    // so add it to media queue.
    $this->addVideoToRequestQueue($hash, $url, $collectionId);
    return response()->json([
        'status_code' => 201,
        'message' => 'Requested video was added to media queue for processing.'
      ], 201);
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
        ->update(['status' => 'done']);
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

  protected function normalizeUrl($url)
  {
    $parsed = parse_url($url);
    $host = $parsed['host'];
    $stem = str_replace('www.', '', $host);
    $domain = stristr($stem, '.',true);
    $result = '';

    switch ($domain) {
      case 'youtube':
      case 'youtu': // youtu.be
        $result = $this->normalizeYoutubeUrl($parsed);
        break;

      default:
        $result = $url;
        break;
    }

    return $result;
  }

  protected function normalizeYoutubeUrl($parsedUrl)
  {
    $normalized = 'https://www.youtube.com/watch?';

    // youtube.com
    if (str_contains($parsedUrl['host'], 'youtube')) {
      $query = $parsedUrl['query'];
      $qs = explode('&', $query);

      foreach ($qs as &$q) {
        if (starts_with($q, 'v=')) {
          $normalized .= $q;
          break;
        }
      }
      return $normalized;
    }

    // youtu.be
    if (str_contains($parsedUrl['host'], 'youtu.be')) {
      $normalized .= 'v=' . substr($parsedUrl['host'], 1);
      return $normalized;
    }
  }

  protected function addVideoToRequestQueue($hash, $url, $collectionId)
  {
    $user = \JWTAuth::parseToken()->toUser();

    \DB::table('mediaqueue')
      ->insert([
        'hash' => $hash,
        'url' => $url,
        'requester' => (int) $user->id,
        'collection_id' => (int) $collectionId,
        'status' => 'pending',
        'created_at' => Carbon::now()
      ]);
  }

}