<?php

namespace Mypleasure\Services\Validation\Invite;

use Mypleasure\Services\Validation\AbstractValidator;

class InviteValidator extends AbstractValidator {

  protected $rules = array(
    'email' => 'required|email'
  );

}