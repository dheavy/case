<?php

use Carbon\Carbon;

class TagsController extends \BaseController {

  /**
   * An instance of the Tag model passed via injection, to loosen dependencies and allow easier testing.
   *
   * @var Tag
   */
  protected $tag;

  /**
   * An instance of the Video model passed via injection, to loosen dependencies and allow easier testing.
   *
   * @var Video
   */
  protected $video;

  /**
   * Create instance.
   *
   * @param Tag   $tag   An instance of the Tag model passed via injection, to loosen dependencies and allow easier testing.
   * @param Video $video An instance of the Video model passed via injection, to loosen dependencies and allow easier testing.
   */
  public function __construct(Tag $tag, Video $video)
  {
    $this->tag = $tag;
    $this->video = $video;
    parent::__construct();
  }

  /**
   * Update the tags for a video.
   *
   * @return Illuminate\Http\RedirectResponse
   */
  public function update()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    // Get user.
    $user = Auth::user();

    // Get tags as list of strings, exploded into an array.
    $inputTags = strtolower(Input::get('tags', ''));
    $inputTagsArray = explode(',', $inputTags);
    foreach ($inputTagsArray as $index => $name) {
      // Remove blanks.
      if (trim($name) == '') {
        unset($inputTagsArray[$index]);
        continue;
      }
      // Trim names.
      $inputTagsArray[$index] = trim($name);
    }

    // Get related video.
    $videoId = (integer)Input::get('video', '');
    $video = $this->video->find($videoId);

    // Create new tags and attach them to the video.
    foreach ($inputTagsArray as $tagName) {
      $tag = $this->generateTagFromName($tagName);
      if (!$video->tags->contains($tag->id)) {
        $video->tags()->attach($tag->id);
      }
    }

    // Remove tags not used anymore.
    if (count($inputTagsArray) !== $video->tags->count()) {
      $tags = &$video->tags;
      $tagsFn = $video->tags();
      $tags->each(function($tag) use (&$inputTagsArray, &$tags, &$tagsFn) {
        $tagName = $tag->name;
        if (!in_array($tagName, $inputTagsArray) && $tags->contains($tag->id)) {
          $tagsFn->detach($tag->id);
        }
      });
    }

    return Redirect::route('videos.index')->with('message', 'Tags updated.');
  }

  /**
   * Check if proposed tag exists, return it if it does,
   * create it then return it if it does not.
   *
   * @param  string $name The tag name.
   * @return Tag  Either a new instance, or an instance fetched from DB.
   */
  public function generateTagFromName($name)
  {
    $name = $this->slugify(trim($name));
    $tag = $this->tag->fetchOrCreate($name);
    return $tag;
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

    // Return view.
    return View::make('tags.edit')->with(array('user' => $user, 'video' => $video, 'tags' => $tags));
  }

}