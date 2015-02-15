<?php

namespace Mypleasure\Services\Validation\Reminder;

use Mypleasure\Services\Validation\AbstractValidator;

class ResetPasswordValidator extends AbstractValidator {

  protected $rules = array(
    'email'                 => 'required|email',
    'password'              => array('required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24', 'confirmed'),
    'password_confirmation' => array('required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24')
  );

}