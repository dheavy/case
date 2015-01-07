<?php

namespace Mypleasure\Services\Validation\User;

use Mypleasure\Services\Validation\AbstractValidator;

/**
 * UserCreateValidator is a validator used for User creation.
 */

class UserCreateValidator extends AbstractValidator {

  protected $rules = array(
    'username'              => 'required|alpha_num|unique:users|between:2,25',
    'email'                 => 'required|email|unique:users',
    'password'              => 'required|alpha_num|between:6,16|confirmed',
    'password_confirmation' => 'required|alpha_num|between:6,16'
  );

}
