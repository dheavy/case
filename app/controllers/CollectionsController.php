<?php

/**
 * CollectionsController is used to manage Collection directly as a resource.
 */

class CollectionsController extends \BaseController {

  /**
   * Create instance.
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Update collection resource.
   *
   * @return Illuminate\Http\RedirectResponse
   */
  public function update()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');

    $user = Auth::user();

    // TODO: sanitize inputs.
    $collection = Collection::findOrFail((int)Input::get('collection', 0));
    $status = (int)Input::get('status', $collection->status);
    $newName = Input::get('name', 'untitled collection');

    $collection->name = $newName;
    $collection->status = $status;
    $collection->slug = $this->slugify($collection->name);
    $saved = $collection->save();

    if (!$saved) return Redirect::route('videos.index')->with('message', 'Oops... an error has occured. Please try again.');

    return Redirect::route('videos.index')->with('message', 'Collection updated.');
  }

  /**
   * Create a collection for a User.
   *
   * @param  mixed   $userId         ID of the user.
   * @param  string  $collectionName Name for the new collection.
   * @return Collection  The created Collection if successfully created, or null otherwise.
   */
  public function createUserCollection($userId, $collectionName)
  {
    $collection = new Collection;
    $collection->name = $collectionName;
    $collection->slug = $this->slugify($collection->name);
    $collection->user_id = $userId;

    // Status is currently set to public by default.
    $collection->status = 1;

    $collection->save();
    return $collection;
  }

  /**
   * Display the "edit collection" form.
   *
   * @param  integer $collectionId The ID of the collection to edit.
   * @return Illuminate\Http\RedirectResponse
   */
  public function getEditCollection($collectionId)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();

    $collection = Collection::findOrFail($collectionId);

    if ($user->hasCollection($collectionId)) {
      return View::make('collections.edit')->with(array('user' => $user, 'collection' => $collection));
    }

    return Redirect::route('videos.index');
  }

  /**
   * Display the "delete collection" view.
   *
   * @param  integer $collectionId The ID of the collection to delete.
   * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
   */
  public function getDeleteCollection($collectionId)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();

    // Get collection and redirect if user <-> collection don't match.
    $collection = Collection::findOrFail($collectionId);
    if (!$user->hasCollection($collectionId)) return Redirect::route('videos.index');

    // Set up variables used in view, including vars for select list.
    $hasVideos = (bool)$collection->videos->count();
    $replaceSelectList = array('' => 'Delete those suckers. FOREVER. BOOM!');

    $user->collections->each(function($c) use (&$replaceSelectList, &$collection) {
      if ($c->id !== $collection->id) {
        $replaceSelectList[$c->id] = 'Move them to ' . $c->name;
      }
    });

    // Display view.
    return View::make('collections.delete')
      ->with(array('user' => $user, 'collection' => $collection, 'hasVideos' => $hasVideos, 'replaceSelectList' => $replaceSelectList));

  }

  /**
   * Destroy collection. Proceed with destroying videos,
   * or moving them to another collection, depending on user's choice.
   *
   * @return Illuminate\Http\RedirectResponse
   */
  public function destroy()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();

    $collection = Collection::findOrFail(Input::get('collection', 0));

    if (!$user->hasCollection($collection->id)) return Redirect::route('videos.index');

    // An empty $replace indicates no replacement collection is provided.
    $replace = Input::get('replace', '');

    // Destroy videos or move to another collection, accordingly.
    if (trim($replace) === '') {
      $collection->dispose();
    } else {
      $replaceCollection = Collection::findOrFail((int)$replace);
      if ($user->hasCollection($replaceCollection->id)) {
        $updated = DB::table('collection_video')
          ->where('collection_id', $collection->id)
          ->update(array('collection_id' => $replaceCollection->id));
        $collection->dispose();
      }
    }

    return Redirect::route('videos.index')->with('message', 'Collection deleted.');
  }

  /**
   * Batch delete collections from a list. Deletes videos as well.
   *
   * @param  Illuminate\Database\Eloquent\Collection $collections The list of collections.
   * @return bool True if deleted successfully.
   */
  public function destroyCollections($collections)
  {
    $collections->each(function($c) {
      $collection = Collection::findOrFail($c['id']);
      $collection->dispose();
    });

    return true;
  }

}