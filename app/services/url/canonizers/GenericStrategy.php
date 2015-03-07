<?php

namespace Mypleasure\Services\Url\Canonization;

/**
 * GenericStrategy canonizes the URL by removing the possibly existing query part.
 * It provides a simple generic implementation for most websites using RESTful URLs.
 */

class GenericStrategy implements StrategeableInterface {

  /**
   * Canonize the URL.
   *
   * @param  string $url The URL to canonize.
   * @return string The canonic URL.
   */
  public function canonize($url)
  {
    $url = trim($url, '!"#$%&\'()*+,-./@:;<=>[\\]^_`{|}~');
    $url = strtok($url, '?');
    return $url;
  }

}