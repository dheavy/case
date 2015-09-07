<?php

namespace Mypleasure\Api\V1\Controller;
use Mypleasure\Http\Requests\SendInviteRequest;

class InviteController extends BaseController {

  public function __construct()
  {
    $this->middleware('api.auth', ['only' => ['send']]);
    $this->middleware('guest', ['only' => ['claim']]);
  }

  public function send(SendInviteRequest $request)
  {

  }

  public function claim()
  {

  }

}