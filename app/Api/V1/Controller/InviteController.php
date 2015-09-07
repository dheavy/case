<?php

namespace Mypleasure\Api\V1\Controller;

class InviteController extends BaseController {

  public function __construct()
  {
    $this->middleware('api.auth', ['only' => ['send']]);
    $this->middleware('guest', ['only' => ['claim']]);
  }

  public function send()
  {

  }

  public function claim()
  {

  }

}