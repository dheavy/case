<?php

/**
 * CollectionsController is used to manage Collection directly as a resource.
 */

class CollectionsController extends \BaseController {

  /**
   * An instance of the Collection model passed via injection, to loosen dependencies and allow easier testing.
   *
   * @var Collection
   */
  protected $collection;

  /**
   * Create instance.
   *
   * @param Collection $collection An instance of the User model passed via injection, to loosen dependencies and allow easier testing.
   */
  public function __construct(Collection $collection)
  {
    parent::__construct();
    $this->collection = $collection;
  }

  /**
   * Create a collection for a User.
   *
   * @param  integer $userId         ID of the user.
   * @param  string  $collectionName Name for the new collection.
   * @return boolean True if succeeded, false otherwise.
   */
  public function createUserCollection($userId, $collectionName)
  {
    $this->collection->name = $collectionName;
    $this->collection->slug = $this->slugify($this->collection->name);
    $this->collection->user_id = $userId;

    // Status is currently set to public by default.
    $this->collection->status = 1;

    $saved = $this->collection->save();
    if (!$saved) return false;

    return true;
  }

}