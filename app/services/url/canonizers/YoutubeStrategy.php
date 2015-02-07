<?php

namespace Mypleasure\Services\Url\Canonization;

/**
 * YoutubeStrategy canonizes the URL from Youtube.
 */

class YoutubeStrategy implements StrategeableInterface {

  /**
   * Canonize the URL.
   *
   * @param  string $url The URL to canonize.
   * @return string The canonic URL.
   */
  public function canonize($url)
  {
    // TODO: See edge cases to make real canonization.
    return $url;
  }

}