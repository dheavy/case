<?php

namespace Mypleasure\Services\Validation\User;

use Mypleasure\Services\Validation\AbstractValidator;

/**
 * UserUpdatePasswordValidator is a validator used when attempting to update a User's password.
 */

class UserUpdatePasswordValidator extends AbstractValidator {

  protected $rules = array(
    'current_password'      => array('required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24'),
    'password'              => array('required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24', 'confirmed'),
    'password_confirmation' => array('required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24')
  );

}
