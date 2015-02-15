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
    'password'              => array('required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24', 'confirmed'),
    'password_confirmation' => array('required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24')
  );

}
