<?php

namespace Mypleasure\Services\Validation\User;

use Mypleasure\Services\Validation\AbstractValidator;

/**
 * UserUpdateEmailValidator is a validator used when attempting to update a User's email.
 */

class UserUpdateEmailValidator extends AbstractValidator {

  protected $rules = array(
    'email' => 'email|unique:users'
  );

}
