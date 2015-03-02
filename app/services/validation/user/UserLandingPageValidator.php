<?php

namespace Mypleasure\Services\Validation\User;

use Mypleasure\Services\Validation\AbstractValidator;

/**
 * UserLandingPageValidator is a validator used for User creation via the landing page.
 */

class UserLandingPageValidator extends AbstractValidator {

  protected $rules = array(
    'username' => 'required|alpha_num|unique:users|between:2,25',
    'email'    => 'required|email|unique:users',
    'invite'   => 'required',
    'password' => array('required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24')
  );

}
