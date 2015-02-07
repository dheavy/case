<?php

use Carbon\Carbon;

class TagsController extends \BaseController {

  protected $tag;
  protected $video;

  public function __construct(Tag $tag, Video $video)
  {
    $this->tag = $tag;
    $this->video = $video;
    parent::__construct();
  }

  public function getEditTags($videoId)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    // Get video.
    $video = $this->video->find($videoId);

    // Fetch tags for this video into an array of tag names.
    $tagsCollection = $video->tags;
    $tagsArray = array();
    $tagsCollection->each(function($tag) use (&$tagsArray) {
      $tagsArray[] = $tag->name;
    });

    // Format array into a string we'll display to the user.
    $tags = join(', ', $tagsArray);

    // Build URL for form action.
    $url = URL::route('user.tags.edit', $video->id);

    // Return view.
    return View::make('tags.edit')->with(array('user' => $user, 'video' => $video, 'tags' => $tags, 'url' => $url));
  }

  public function update()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    $taintedTags = Input::get('tags', '');
    $taintedTags = strtolower($taintedTags);
    $taintedTagsArray = explode(',', $taintedTags);

    $videoId = (integer)Input::get('video', '');
    $video = $this->video->find($videoId);

    foreach ($taintedTagsArray as $tagName) {
      $tag = $this->generateTagFromName($tagName);
      if (!$video->tags->contains($tag->id)) {
        $video->tags()->attach($tag->id);
      }
    }

    return Redirect::route('user.videos')->with('message', 'Tags updated.');
  }

  public function generateTagFromName($name)
  {
    $name = $this->slugify(trim($name));
    $tag = $this->tag->fetchOrCreate($name);
    return $tag;
  }

}