<?php

namespace Mypleasure\Services\Validation\Collection;

use Mypleasure\Services\Validation\AbstractValidator;

/**
 * CollectionValidator is a validator used for Collection creation and update.
 */
class CollectionValidator extends AbstractValidator {

  protected $rules = array(
    'name' => 'required|between:2,30',
    'status' => 'required|numeric'
  );

}