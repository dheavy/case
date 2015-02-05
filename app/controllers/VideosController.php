<?php

use Carbon\Carbon;

/**
 * VideosController deals with video resources for a User,
 * from creating instance from MongoDB to displaying views.
 */

class VideosController extends \BaseController {

  /**
   * Create instance.
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * List a User's videos in a dedicated view.
   *
   * @return Illuminate\View\View
   */
  public function index()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    // Get and consolidate videos user might have waiting for herself.
    // Retrieve the number of videos possibly still pending while user displays her videos page.
    $pending = $this->fetchNewlyCurated($user->id);

    // Aggregate all videos now, and build view.
    $videos = $user->videos()->reverse();

    return View::make('user.videos', array('videos' => $videos, 'user' => $user, 'pending' => $pending));
  }

  /**
   * Store a video for the User.
   *
   * @return Illuminate\Http\RedirectResponse
   */
  public function store()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    // Clean up input, remove GET variables, ensure it is a proper URL.
    $url = $this->cleanupUrlInput(Input::get('url', ''));

    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
      return Redirect::route('user.videos.add')->with('message', 'URL not valid. Please try again.');
    }

    // Create video hash.
    $hash = md5(urlencode(utf8_encode($url)));

    // Stop here if user already added this video.
    if ($user->hasVideoFromHash($hash)) {
      return Redirect::route('user.videos.add')->with('message', "Oops... It looks like you've already added this video!");
    }

    // Check if video already exist in videostore.
    $video = $this->retrieveVideoInStoreFromHash($hash);

    // If video does exist, create an instance for this user.
    // Otherwise, add to queue in MongoDB.
    $exists = (bool)$video;
    if ($exists) {
      $this->createVideoInstance($user->collections[0]->id, $video[0]);
    } else {
      // The ObjectID is based on the hash of the video to look for dups.
      // It is reduced to 24 characters match ObjectID's requirements.
      try {
        $this->addVideoRequestToQueue($hash, $url, $user->id);
      } catch (MongoDuplicateKeyException $error) {
        return Redirect::route('user.profile')->with('message', 'Your video is already being processed and will show up in your collection in a short moment.');
      }
    }

    // Redirect user with a short message.
    return Redirect::route('user.profile')->with('message', 'Your videos has been added to processing queue and will be available shortly.');
  }

  /**
   * Batch delete videos from a list.
   *
   * @param  Illuminate\Database\Eloquent\Collection $videos The list of videos.
   * @throws ModelNotFoundException
   * @return bool True if deleted successfully.
   */
  public function destroyVideos($videos)
  {
    $videos->each(function($v) {
      $video = Video::findOrFail($v['id']);
      $video->delete();
    });

    return true;
  }

  /**
   * Poll the queue to find possible videos ready for User.
   *
   * @param  integer $userId ID of the current user.
   * @return void
   */
  protected function fetchNewlyCurated($userId)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    $pending = $this->fetchNewAndPending();
    $this->fetchNewAndReady();

    return $pending;
  }

  /**
   * Gives information about the number of videos still pending between
   * two cron jobs while user request to see her videos in the /videos page.
   *
   * @return integer The number of videos still pending at the time.
   */
  protected function fetchNewAndPending()
  {
    // Check if we have videos ready for this user in the 'queue' collection.
    $pending = DB::connection('mongodb')
      ->collection('queue')
      ->where('status', '=', 'pending')
      ->where('requester', '=', $userId)
      ->get();

    return count($pending);
  }

  /**
   * Process the videos in store ready for this user.
   * Update queue status for these videos and invoke Video model creation.
   *
   * @return void
   */
  protected function fetchNewAndReady()
  {
    // Check if we have videos ready for this user in the 'queue' collection.
    $ready = DB::connection('mongodb')
      ->collection('queue')
      ->where('status', '=', 'ready')
      ->where('requester', '=', $userId)
      ->get();

    if (count($ready) === 0) return;

    // If we do, collect them as instances from the 'videos' collection.
    foreach($ready as $v) {
      $instance = DB::connection('mongodb')
        ->collection('videos')
        ->where('hash', '=', $v['hash'])
        ->first();

      $this->createVideoInstance($user->collections[0]->id, $instance);

      // Mark as 'done'.
      DB::connection('mongodb')
        ->collection('queue')
        ->where('status', '=', 'ready')
        ->where('requester', '=', $userId)
        ->update(array('status' => 'done'));
    }
  }

  /**
   * Creates an Eloquent Video model from the data fetched from the videostore.
   *
   * @param  integer $collectionId ID of the collection to attach this video to.
   * @param  array   $instance     The data extracted from the video in the MongoDB storage.
   * @return Illuminate\Http\RedirectResponse
   */
  protected function createVideoInstance($collectionId, $instance)
  {
    $now = Carbon::now()->toDateTimeString();

    // Populate video instance with data from the videostore.
    $video = new Video;
    $video->hash = $instance['hash'];
    $video->title = $instance['title'];
    $video->poster = $instance['poster'];
    $video->method = $instance['method'];
    $video->original_url = $instance['original_url'];
    $video->embed_url = $instance['embed_url'];
    $video->duration = $instance['duration'];
    $video->slug = $this->slugify($video->title);
    $video->created_at = $now;
    $video->updated_at = $now;

    // Save video in DB.
    $video->save();

    // Create relationship with Collection and redirect user.
    $created = DB::table('collection_video')
      ->insert(array(
        'collection_id' => $collectionId,
        'video_id' => $video->id,
        'created_at' => $now,
        'updated_at' => $now
      ));
  }

  /**
   * Clean URL passed as argument to canonize it for insertion in video store.
   *
   * @param  string $taintedUrl The original URL.
   * @return string The cleaned-up URL.
   */
  protected function cleanupUrlInput($taintedUrl)
  {
    $url = trim($url, '!"#$%&\'()*+,-./@:;<=>[\\]^_`{|}~');
    $url = strtok($url, '?');
    return $url;
  }

  /**
   * Retrieve a video in store based on its hash.
   *
   * @param  string $hash The hash of the video to retrieve.
   * @return Illuminate\Database\Eloquent\Collection|null
   */
  protected function retrieveVideoInStoreFromHash($hash)
  {
    $video = DB::connection('mongodb')
      ->collection('videos')
      ->where('hash', '=', $hash)
      ->get();

    return $video;
  }

  /**
   * Add a video request to the queue.
   *
   * @param string  $hash      The hash of the requested video.
   * @param string  $url       The URL of the requested video.
   * @param integer $requester The ID of the User making the request.
   */
  protected function addVideoRequestToQueue($hash, $url, $requester)
  {
    $request = DB::connection('mongodb')
      ->collection('queue')
      ->insert(array(
        '_id' => new MongoId(substr($hash, 0, 24)),
        'hash' => $hash,
        'url' => $url,
        'requester' => $requester,
        'status' => 'pending',
        'created_at' => Carbon::now()->toDateTimeString(),
        'updated_at' => Carbon::now()->toDateTimeString()
      ));
  }

}