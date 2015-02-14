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

  public function feed()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    $videos = array();
    $users = User::all()->reverse();

    $users->each(function($user) use (&$videos) {
      if ($user->videos()->count() > 0) {
        $videosList = $user->videos()->reverse();
        $videosList->each(function($video) use (&$videos, &$user) {
          if ($video->isPublic()) {
            $v = new stdClass;
            $v->user = $user;
            $v->video = $video;
            $videos[] = $v;
          }
        });
      }
    });

    return View::make('videos.feed')->with('videos', $videos);
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

    // Create a multidimensional array containing arrays, each holding
    // a collection id and name, and a sub-array of its videos.
    $collections = array();
    $collectionsList = $user->collections->reverse();
    $collectionsList->each(function($collectionModel) use (&$collections) {
      $collection = new stdClass;
      $collection->id = $collectionModel->id;
      $collection->name = $collectionModel->name;
      $collection->videos = array();
      $videoList = $collectionModel->videos;
      $videoList->each(function($videoModel) use (&$collection) {
        $collection->videos[] = $videoModel;
      });
      $collection->videos = array_reverse($collection->videos);
      $collections[] = $collection;
    });

    // Make view.
    return View::make('videos.index', array('collections' => $collections, 'user' => $user, 'pending' => $pending));
  }

  /**
   * Display "add video" form.
   *
   * @return Illuminate\View\View
   */
  public function getAddVideo()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();
    $collections = array();
    $user->collections->each(function($collection) use (&$collections) {
      $collections[$collection->id] = $collection->name;
    });
    return View::make('videos.create')->with(array('user' => $user, 'collections' => $collections));
  }

  /**
   * Display "add fake video" page.
   *
   * @return Illuminate\View\View
   */
  public function getAddVideoDebug()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();
    return View::make('debug.addvideo')->with('user', $user);
  }

  /**
   * Display the "edit video" form.
   *
   * @param  integer $videoId The ID of the Video to edit.
   * @return Illuminate\Http\RedirectResponse
   */
  public function getEditVideo($videoId)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user and video.
    $user = Auth::user();
    $video = Video::findOrFail($videoId);

    return View::make('videos.edit')->with(array('user' => $user, 'video' => $video));
  }

  /**
   * Display the "delete video" page.
   *
   * @param  integer $videoId The ID of the Video to delete
   * @return Illuminate\Http\RedirectResponse
   */
  public function getDeleteVideo($videoId)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user and video.
    $user = Auth::user();
    $video = Video::findOrFail($videoId);

    return View::make('videos.delete')->with(array('user' => $user, 'video' => $video));
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
      return Redirect::route('videos.create')->with('message', 'URL not valid. Please try again.');
    }

    // Get collection to add the Video to.
    // If input 'collection' is an empty string, create a new collection.
    $collectionId = trim(Input::get('collection', ''));
    if ($collectionId === '') {
      // Create new collection.
      // TODO: Sanitize input.
      $collectionName = Input::get('name', '');
      if (trim($collectionName) === '') {
        $collectionName = 'untitled collection';
      }

      $collection = App::make('CollectionsController')->createUserCollection($user->id, $collectionName);
      $collectionId = $collection->id;
      if (!$collectionId) {
        return Redirect::route('videos.create')->with('message', 'Oops... There was an error adding this videos...');
      }
    } else {
      $collectionId = (int)$collectionId;
    }

    // Canonize URL.
    $url = $this->urlSanitizer->canonize($url);

    // Create video hash.
    $hash = md5(urlencode(utf8_encode($url)));

    // Stop here if user already added this video.
    if ($user->hasVideoFromHash($hash)) {
      return Redirect::route('videos.create')->with('message', "Oops... It looks like you've already added this video!");
    }

    // Check if video already exist in videostore.
    $video = $this->retrieveVideoInStoreFromHash($hash);

    // If video does exist, create an instance for this user.
    // Otherwise, add to queue in MongoDB.
    $exists = (bool)$video;
    if ($exists) {
      $created = $this->createVideoInstance($collectionId, $video[0]);
      if (!(bool)$created) {
        return Redirect::route('users.profile')->with('message', 'Oops.. there was an error adding a video.');
      }
    } else {
      // The ObjectID is based on the hash of the video to look for dups.
      // It is reduced to 24 characters match ObjectID's requirements.
      try {
        $this->addVideoRequestToQueue($hash, $url, $user->id, $collectionId);
      } catch (MongoDuplicateKeyException $error) {
        return Redirect::route('users.profile')->with('message', 'Your video is already being processed and will show up in your collection in a short moment.');
      }
    }

    // Redirect user with a short message.
    return Redirect::route('users.profile')->with('message', 'Your video has been added to processing queue and will be available shortly.');
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

    return Redirect::route('users.profile')->with('message', 'Fake video added to your list of videos.');
  }

  /**
   * Update Videos resource.
   *
   * @return Illuminate\Http\RedirectResponse
   */
  public function update()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    $video = Video::findOrFail((int)Input::get('video', 0));
    if ($user->hasVideo($video->id)) {
      // TODO: Sanitize input.
      $video->title = Input::get('title', '');
      if (trim($video->title) === '') {
        $video->title = 'untitled video';
      }
      $saved = $video->save();
      if (!$saved) {
        return Redirect::route('video.edit', [$video->id])
          ->with('message', 'Oops... there was an error updating your video. Please try again.');
      }
      return Redirect::route('videos.index')
          ->with('message', 'Your video has been updated.');
    }
    return Redirect::route('videos.index');
  }

  /**
   * Destroy Video resource.
   *
   * @param  integer $id The ID of the Video to destroy.
   * @return Illuminate\Http\RedirectResponse
   */
  public function destroy($id)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    $video = Video::findOrFail($id);
    if ($user->hasVideo($video->id)) {
      $video->delete();
      return Redirect::route('videos.index')->with('message', 'Your video has been deleted.');
    }
    return Redirect::route('videos.index');
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
    $this->fetchNewAndReady($user->id);

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
  protected function fetchNewAndReady($userId)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    $user = Auth::user();

    // Check if we have videos ready for this user in the 'queue' collection.
    $ready = $this->getReadyVideos($userId);

    if (count($ready) === 0) return;

    // If we do, collect them as instances from the 'videos' collection.
    foreach($ready as $v) {
      $instance = DB::connection('mongodb')
        ->collection('videos')
        ->where('hash', '=', $v['hash'])
        ->first();

      $collectionId = $v['collection'];

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
   * @param  integer $collectionId ID of the collection to attach this video to.
   * @param  array   $instance     The data extracted from the video in the MongoDB storage.
   * @return boolean  True if successful, false otherwise.
   */
  protected function createVideoInstance($collectionId, $instance)
  {
    $now = Carbon::now()->toDateTimeString();

    // Populate video instance with data from the videostore.
    // Strip poster's url from protocol to adapt to HTTP/HTTPS on the fly.
    $poster = $instance['poster'];
    if (strpos($poster, 'http://') !== false) $poster = str_replace('http://', '//', $poster);
    if (strpos($poster, 'https://') !== false) $poster = str_replace('https://', '//', $poster);

    $video = new Video;
    $video->hash = $instance['hash'];
    $video->title = $instance['title'];
    $video->poster = $poster;
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
   * @param string  $hash         The hash of the requested video.
   * @param string  $url          The URL of the requested video.
   * @param mixed   $requester    The ID of the User making the request.
   * @param mixed   $collectionId The ID of the collection this Video will be added to.
   */
  protected function addVideoRequestToQueue($hash, $url, $requester, $collectionId)
  {
    $request = DB::connection('mongodb')
      ->collection('queue')
      ->insert(array(
        '_id' => new MongoId(substr($hash, 0, 24)),
        'hash' => $hash,
        'url' => $url,
        'requester' => (int)$requester,
        'collection' => (int)$collectionId,
        'status' => 'pending',
        'created_at' => Carbon::now()->toDateTimeString(),
        'updated_at' => Carbon::now()->toDateTimeString()
      ));
  }

}