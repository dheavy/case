<?php

namespace Mypleasure\Services\Providers;

use Illuminate\Support\ServiceProvider;
use Mypleasure\Services\Url\UrlSanitizer;

class UrlServiceProvider extends ServiceProvider {

  public function register()
  {
    $this->app->bind('UrlSanitizer', function($app) {
      return new UrlSanitizer;
    });
  }

}