<?php

namespace Mypleasure\Services\Url;

class UrlSanitizer {

  protected $sites = array(
    'youtube' => 'Youtube',
    'youporn' => 'Generic',
    'xhamster' => 'Generic',
    'xvideos' => 'Generic',
    'dailymotion' => 'Youtube',
    'vimeo' => 'Youtube'
  );

  public function canonize($url)
  {
    $parsed = parse_url($url);
    $host = $parsed['host'];
    $strategyClass = 'Mypleasure\Services\Url\Canonization\\' . $this->findCanonizationStrategy($host) . 'Strategy';
    $strategy = new $strategyClass;

    return $strategy->canonize($url);
  }

  /**
   * Check whether URL is valid or not.
   *
   * @param string $url The URL to validate.
   * @return boolean True if considered valid, false otherwise.
   */
  public function validate($url)
  {
    return filter_var($url, FILTER_VALIDATE_URL);
  }

  protected function findCanonizationStrategy($host)
  {
    foreach ($this->sites as $siteName => $strategy) {
      if (stripos($host, $siteName) !== false) {
        return $strategy;
      }
    }

    return 'Generic';
  }

}