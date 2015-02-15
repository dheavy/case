<?php

namespace Mypleasure\Services\Validation\Reminder;

use Mypleasure\Services\Validation\AbstractValidator;

class RemindPasswordValidator extends AbstractValidator {

  protected $rules = array(
    'email' => 'required|email'
  );

}