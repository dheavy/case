<?php

/**
 * ProfileController manages displaying views from user's profile.
 */

class ProfileController extends \BaseController {

  /**
   * An instance of the User model passed via injection, to loosen dependencies and allow easier testing.
   *
   * @var User
   */
  protected $user;

/**
 * Create instance, assigning currently logged in user to protected variable.
 */
  public function __construct()
  {
    parent::__construct();
    $this->user = Auth::user();
  }

  /**
   * Display user's profile index page.
   *
   * @return Illuminate\View\View
   */
  public function getProfile()
  {
    return View::make('user.profile')->with('user', $this->user);
  }

  /**
   * Display "edit email" form.
   *
   * @return Illuminate\View\View
   */
  public function getEditEmail()
  {
    return View::make('user.editemail')->with('user', $this->user);
  }

  /**
   * Display "edit password" form.
   *
   * @return Illuminate\View\View
   */
  public function getEditPassword()
  {
    return View::make('user.editpassword')->with('user', $this->user);
  }

  /**
   * Display "add video" form.
   *
   * @return Illuminate\View\View
   */
  public function getAddVideo()
  {
    return View::make('user.addvideo')->with('user', $this->user);
  }

  /**
   * Display "add fake video" page.
   *
   * @return Illuminate\View\View
   */
  public function getAddVideoDebug()
  {
    return View::make('debug.addvideo')->with('user', $this->user);
  }

  /**
   * Display "delete account" form
   *
   * @return Illuminate\View\View
   */
  public function getDelete()
  {
    return View::make('user.delete')->with('user', $this->user);
  }

  /**
   * Display the "edit tags" form.
   *
   * @param  mixed $videoId The ID of the video to which the tags belong.
   * @return Illuminate\View\View
   */
  public function getEditTags($videoId)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    // Get video.
    $video = Video::findOrFail($videoId);

    // Fetch tags for this video into an array of tag names.
    $tagsCollection = $video->tags;
    $tagsArray = array();
    $tagsCollection->each(function($tag) use (&$tagsArray) {
      $tagsArray[] = $tag->name;
    });

    // Format array into a string we'll display to the user.
    $tags = join(', ', $tagsArray);

    // Build URL for form action.
    $url = secure_url(URL::route('user.tags.edit', $video->id));

    // Return view.
    return View::make('tags.edit')->with(array('user' => $user, 'video' => $video, 'tags' => $tags, 'url' => $url));
  }

  public function getEditVideo($videoId)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user and video.
    $user = Auth::user();
    $video = Video::findOrFail($videoId);

    $url = secure_url(URL::route('user.videos.edit', $video->id));

    return View::make('user.editvideo')->with(array('user' => $user, 'video' => $video, 'url' => $url));
  }

  public function getDeleteVideo($videoId)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user and video.
    $user = Auth::user();
    $video = Video::findOrFail($videoId);

    // Build URL for form action.
    $url = secure_url(URL::route('user.videos.delete', $video->id));

    return View::make('user.deletevideo')->with(array('user' => $user, 'video' => $video, 'url' => $url));
  }

}