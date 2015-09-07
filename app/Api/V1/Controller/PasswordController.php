<?php

namespace Mypleasure\Api\V1\Controller;

use Mypleasure\Traits\ResetsPasswords;

class PasswordController extends BaseController {

  use ResetsPasswords;

  public function __construct()
  {
      $this->middleware('guest');
  }

}