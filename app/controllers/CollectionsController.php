<?php

class CollectionsController extends \BaseController {

  protected $collection;

  public function __construct(Collection $collection)
  {
    $this->collection = $collection;
  }

  public function createUserCollection($userId, $collectionName)
  {
    $collection = new Collection;
    $collection->name = $collectionName;
    $collection->slug = $this->slugify($collection->name);
    $collection->user_id = $userId;

    // Status is currently set to public by default.
    $collection->status = 1;

    $saved = $collection->save();
    if (!$saved) return false;

    return true;
  }

}