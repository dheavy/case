<?php

/**
 * TagObserver leverages observers in Laravel to allow
 * processing model level events on the Tag model.
 */

class TagObserver {

  /**
   * Before saving model, set a default email if none was given.
   *
   * @param Tag  The tag model about to be saved.
   */
  public function saving(Tag $tag)
  {
    $tag->name = strtolower($tag->name);
  }

}
