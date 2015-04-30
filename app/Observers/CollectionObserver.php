<?php namespace Mypleasure\Observers;

use Mypleasure\Collection;

/**
 * CollectionObserver leverages observers in Laravel to allow
 * processing model level events on the Collection model.
 */

class CollectionObserver {

  /**
   * Before saving model, set a default email if none was given.
   *
   * @param Mypleasure\Collection  The collection model about to be saved.
   */
  public function saving(Collection $collection)
  {
    if ($collection->slug == null) {
      $collection->slugifyName();
    }

    if ($collection->status == null) {
      $collection->status = 1;
    }
  }

}