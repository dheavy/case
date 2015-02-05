<?php

namespace Mypleasure\Services\Validation\User;

use Mypleasure\Services\Validation\AbstractValidator;

/**
 * UserAuthValidator is a validator used when attempting to authenticate a User.
 */

class UserDestroyValidator extends AbstractValidator {

  protected $rules = array(
    'password' => 'required|alpha_num|between:6,16'
  );

}
