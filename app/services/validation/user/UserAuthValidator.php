<?php

namespace Mypleasure\Services\Validation\User;

use Mypleasure\Services\Validation\AbstractValidator;

/**
 * UserAuthValidator is a validator used when attempting to authenticate a User.
 */

class UserAuthValidator extends AbstractValidator {

  protected $rules = array(
    'username'              => 'required|alpha_num|between:2,25',
    'password'              => 'required|alpha_num|between:6,16'
  );

}
