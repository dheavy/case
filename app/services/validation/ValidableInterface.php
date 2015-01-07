<?php

namespace Mypleasure\Services\Validation;

/**
 * ValidableInterface provides an interface on top
 * of which validator classes can establish methods
 * signature and behavior contracts.
 */

interface ValidableInterface {

  public function with(array $input);

  public function passes();

  public function errors();

}
