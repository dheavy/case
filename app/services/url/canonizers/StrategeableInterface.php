<?php

namespace Mypleasure\Services\Url\Canonization;

/**
 * StrategeableInterface provides an interface on top
 * of which URL canonizing classes can establish methods
 * signature and behavior contracts.
 */

interface StrategeableInterface {

  public function canonize($url);

}