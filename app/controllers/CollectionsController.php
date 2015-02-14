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

  public function index()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();

    $collectionsList = $user->collections;
    $collections = array();

    $collectionsList->each(function($c) use (&$collections) {
      $collections[] = array(
        'id' => $c->id,
        'name' => $c->name,
        'status' => (int) $c->status,
        'numVideos' => $c->videos->count()
      );
    });

    return View::make('collections.index')->with(array('user' => $user, 'collections' => $collections));
  }

  public function create()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();

    // TODO: sanitize inputs.
    $name = Input::get('name', '');
    if (trim($name) == '') $name = 'untitled collection';

    $status = (int)Input::get('status', '');
    if ($status !== 0 && $status !== 1) $status = 1;

    $collection = $this->createUserCollection($user->id, $name, $status);
    if (!$collection) return Redirect::route('videos.index')->with('message', 'Oops... an error has occured. Please try again.');

    return Redirect::route('videos.index')->with('message', 'Collection created.');
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
    $newName = Input::get('name', '');
    if (trim($newName) == '') $newName = 'untitled collection';

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
   * @param  integer $status         The visibility status (1 is public, 0 is private). Defaults to 1.
   *
   * @return Collection  The created Collection if successfully created, or null otherwise.
   */
  public function createUserCollection($userId, $collectionName, $status = 1)
  {
    $collection = new Collection;
    $collection->name = $collectionName;
    $collection->slug = $this->slugify($collection->name);
    $collection->user_id = $userId;

    $collection->status = $status;

    $collection->save();
    return $collection;
  }

  /**
   * Display the view for a single collection.
   *
   * @param  integer $collectionId The ID of the collection to view.
   * @return Illuminate\View\View
   */
  public function getCollection($id)
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();

    if (!$user->hasCollection((int)$id)) return Redirect::route('collections.index');

    $collection = Collection::findOrFail($id);

    return View::make('collections.view')->with(array('user' => $user, 'collection' => $collection));
  }

  /**
   * Display the "create collection" form.
   *
   * @return Illuminate\View\View
   */
  public function getCreateCollection()
  {
    if (!Auth::check()) App::abort(401, 'Unauthorized');
    $user = Auth::user();

    return View::make('collections.create')->with('user', $user);
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