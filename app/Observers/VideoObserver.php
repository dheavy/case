<?php namespace Mypleasure\Observers;

use Mypleasure\Video;

/**
 * VideoObserver leverages observers in Laravel to allow
 * processing model level events on the Video model.
 */

class VideoObserver {

  /**
   * Before saving model, create slug from name.
   *
   * @param Mypleasure\Video  The video model about to be saved.
   */
  public function saving(Video $video)
  {
    if ($video->slug == null) {
      $video->slugifyTitle();
    }
  }

}