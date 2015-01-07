<?php

namespace Mypleasure\Services\Validation\User;

use Mypleasure\Services\Validation\AbstractValidator;

/**
 * UserUpdatePasswordValidator is a validator used when attempting to update a User's password.
 */

class UserUpdatePasswordValidator extends AbstractValidator {

  protected $rules = array(
    'current_password'      => 'required|alpha_num|between:6,16',
    'password'              => 'required|alpha_num|between:6,16|confirmed',
    'password_confirmation' => 'required|alpha_num|between:6,16'
  );

}
