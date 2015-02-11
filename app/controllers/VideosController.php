<?php

use Carbon\Carbon;
use Mypleasure\Services\Url\UrlSanitizer;

/**
 * VideosController deals with video resources for a User,
 * from creating instance from MongoDB to displaying views.
 */

class VideosController extends \BaseController {

  /**
   * An instance of UrlSanitizer.
   *
   * @var Mypleasure\Services\Url\UrlSanitizer
   */
  protected $urlSanitizer;

  /**
   * Create instance.
   */
  public function __construct(UrlSanitizer $urlSanitizer)
  {
    parent::__construct();
    $this->urlSanitizer = $urlSanitizer;
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

    // Ensure URL validity and canonize it.
    $url = Input::get('url', '');

    if (!$this->urlSanitizer->validate($url)) {
      return Redirect::route('user.videos.add')->with('message', 'URL not valid. Please try again.');
    }

    $url = $this->urlSanitizer->canonize($url);

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
      $created = $this->createVideoInstance($user->collections[0]->id, $video[0]);
      if (!(bool)$created) {
        return Redirect::route('user.profile')->with('message', 'Oops.. there was an error adding a video.');
      }
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
    return Redirect::route('user.profile')->with('message', 'Your video has been added to processing queue and will be available shortly.');
  }

  /**
   * Store a dummy video for user. Use it for development and test purpose.
   *
   * @return Illuminate\Http\RedirectResponse
   */
  public function storeDebug()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    // Create instance from dummy data.
    $now = Carbon::now()->toDateTimeString();
    $faker = Faker\Factory::create();
    $seed = $faker->word . (string)rand(1, 10000);
    $originalUrl = 'http://example.com/' . $seed;
    $hash = md5(urlencode(utf8_encode($originalUrl)));
    $title = $faker->word;

    $data = array(
      'hash' => $hash,
      'title' => $title,
      'poster' => $originalUrl . '/poster.jpg',
      'method' => '_dummy',
      'original_url' => $originalUrl,
      'embed_url' => $originalUrl . '/embed',
      'duration' => '00:10:05'
    );

    $this->createVideoInstance($user->collections[0]->id, $data);

    return Redirect::route('user.profile')->with('message', 'Fake video added to your list of videos.');
  }

  public function edit()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    $video = Video::findOrFail((int)Input::get('video', 0));
    if ($user->hasVideo($video->id)) {
      // TODO: Sanitize input.
      $video->title = Input::get('title', '');
      $video->save();
      if (!$saved) {
        return Redirect::route('user.videos.edit', [$video->id])
          ->with('message', 'Oops... there was an error updating your video. Please try again.');
      }
      return Redirect::route('user.videos')
          ->with('message', 'Your video has been updated.');
    }
    return Redirect::route('user.videos');
  }

  public function destroy($id)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    $video = Video::findOrFail($id);
    if ($user->hasVideo($video->id)) {
      $video->delete();
      return Redirect::route('user.videos')->with('message', 'Your video has been deleted.');
    }
    return Redirect::route('user.videos');
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
   * @param  mixed $userId ID of the current user.
   * @return void
   */
  protected function fetchNewlyCurated($userId)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    $pending = $this->fetchNewAndPending($user->id);
    $this->fetchNewAndReady($user->id, $user->collections[0]->id);

    return $pending;
  }

  /**
   * Gives information about the number of videos still pending between
   * two cron jobs while user request to see her videos in the /videos page.
   *
   * @param  mixed $userId  The ID of the user.
   * @return integer The number of videos still pending at the time.
   */
  protected function fetchNewAndPending($userId)
  {
    $pending = $this->getPendingVideos($userId);
    return count($pending);
  }

  /**
   * Process the videos in store ready for this user.
   * Update queue status for these videos and invoke Video model creation.
   *
   * @param  mixed $userId  The ID of the user.
   * @return boolean  True if successful, false otherwise.
   */
  protected function fetchNewAndReady($userId, $collectionId)
  {
    // Check if we have videos ready for this user in the 'queue' collection.
    $ready = $this->getReadyVideos($userId);

    if (count($ready) === 0) return;

    // If we do, collect them as instances from the 'videos' collection.
    foreach($ready as $v) {
      $instance = DB::connection('mongodb')
        ->collection('videos')
        ->where('hash', '=', $v['hash'])
        ->first();

      $created = $this->createVideoInstance($collectionId, $instance);
      if (!(bool)$created) return false;

      // Mark as 'done'.
      DB::connection('mongodb')
        ->collection('queue')
        ->where('status', '=', 'ready')
        ->where('requester', '=', $userId)
        ->update(array('status' => 'done'));
    }

    return true;
  }

  /**
   * Creates an Eloquent Video model from the data fetched from the videostore.
   *
   * @param  mixed   $collectionId ID of the collection to attach this video to.
   * @param  array   $instance     The data extracted from the video in the MongoDB storage.
   * @return boolean  True if successful, false otherwise.
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
    $saved = $video->save();

    if (!(bool)$saved) return false;

    // Create relationship with Collection and redirect user.
    $created = DB::table('collection_video')
      ->insert(array(
        'collection_id' => $collectionId,
        'video_id' => $video->id,
        'created_at' => $now,
        'updated_at' => $now
      ));

    return (bool)$created;
  }

  /**
   * Fetch from MongoDB queue collection the video documents
   * marked 'ready', matching the passed argument as requester.
   *
   * @param  mixed $userId The ID of the user to match as requester.
   * @return Illuminate\Support\Collection The results from the queue.
   */
  protected function getReadyVideos($userId)
  {
    return DB::connection('mongodb')
      ->collection('queue')
      ->where('status', '=', 'ready')
      ->where('requester', '=', $userId)
      ->get();
  }

  /**
   * Fetch from MongoDB queue collection the video documents
   * marked 'pending', matching the passed argument as requester.
   *
   * @param  mixed $userId The ID of the user to match as requester.
   * @return Illuminate\Support\Collection The results from the queue.
   */
  protected function getPendingVideos($userId)
  {
    return DB::connection('mongodb')
      ->collection('queue')
      ->where('status', '=', 'pending')
      ->where('requester', '=', $userId)
      ->get();
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
   * @param mixed   $requester The ID of the User making the request.
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