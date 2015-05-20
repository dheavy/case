<?php namespace Mypleasure\Observers;

use Mypleasure\Collection;

/**
 * CollectionObserver leverages observers in Laravel to allow
 * processing model level events on the Collection model.
 */

class CollectionObserver {

  /**
   * Before saving model, create slug from name, and set private status.
   *
   * @param Mypleasure\Collection  The collection model about to be saved.
   */
  public function saving(Collection $collection)
  {
    if ($collection->slug == null) {
      $collection->slugifyName();
    }

    if ($collection->private == null) {
      $collection->private = false;
    }
  }

}