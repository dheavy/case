<?php

namespace Mypleasure\Tests\Helpers;

/**
 * HttpVerbsTestingTrait should be added to controllers testers
 * classes calling HTTP requests to enable shorthand such
 * as $this->get(), $this->post()...
 */
trait HttpVerbsTestingTrait {

  public function __call($method, $args)
  {
    if (in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
      return $this->call($method, $args[0]);
    }

    throw new BadMethodCallException;
  }

}
