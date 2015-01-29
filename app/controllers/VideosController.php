<?php

use Carbon\Carbon;

class VideosController extends \BaseController {

  protected $video;

  public function __construct(Video $video)
  {
    $this->video = $video;
  }

  public function index()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    $this->fetchNewlyCurated($user->id);

    // Aggregate all videos now, and build view.
    $videos = $user->videos();

    return View::make('user.videos', array('videos' => $videos, 'user' => $user));
  }

  public function store()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    // Clean up input, remove GET variables, ensure it is a proper URL.
    $url = trim(Input::get('url', ''), '!"#$%&\'()*+,-./@:;<=>[\\]^_`{|}~');
    $url = strtok($url, '?');
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
      return Redirect::route('user.videos.add')
        ->with('message', 'URL not valid. Please try again.');
    }

    // Create video hash.
    $hash = md5(urlencode(utf8_encode($url)));

    // Stop here if user already added this video.
    if ($user->hasVideoFromHash($hash)) {
      return Redirect::route('user.videos.add')
        ->with('message', "Oops... It looks like you've already added this video!");
    }

    // Check if video already exist in videostore.
    $video = DB::connection('mongodb')
      ->collection('videos')
      ->where('hash', '=', $hash)
      ->get();

    // If video does exist, create an instance for this user.
    // Otherwise, add to queue in MongoDB.
    $exists = (bool)$video;
    if ($exists) {
      $this->createVideoInstance($user->collections[0]->id, $video[0]);
    } else {
      // The ObjectID is based on the hash of the video to look for dups.
      // It is reduced to 24 characters match ObjectID's requirements.
      try {
        DB::connection('mongodb')
        ->collection('queue')
        ->insert(array(
          '_id' => new MongoId(substr($hash, 0, 24)),
          'hash' => $hash,
          'url' => $url,
          'requester' => $user->id,
          'status' => 'pending',
          'created_at' => Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon::now()->toDateTimeString()
        ));
      } catch (MongoDuplicateKeyException $error) {
        return Redirect::route('user.profile')
          ->with('message', 'Your video is already being processed and will show up in your collection in a short moment.');
      }
    }

    // Redirect user with a short message.
    return Redirect::route('user.profile')
        ->with('message', 'Your videos has been added to processing queue and will be available shortly.');
  }

  public function fetchNewlyCurated($userId)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

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

  protected function createVideoInstance($collectionId, $instance)
  {
    $now = Carbon::now()->toDateTimeString();

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

    $saved = $video->save();

    if (!$saved) {
      return Redirect::route('user.videos.add')
        ->with('message', 'Oops! There was an error adding your video. Please try again later...');
    }

    $created = DB::table('collection_video')
      ->insert(array(
        'collection_id' => $collectionId,
        'video_id' => $video->id,
        'created_at' => $now,
        'updated_at' => $now
      ));

    if (!$created) {
      return Redirect::route('user.videos.add')
        ->with('message', 'Oops! There was an error adding your video. Please try again later...');
    }

    return Redirect::route('user.videos.add')
      ->with('message', 'Video added successfully.');
  }

}