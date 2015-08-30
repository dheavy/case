<?php

namespace Mypleasure\Api\V1\Controller;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;

class AuthController extends BaseController {

  public function authenticate(Request $request)
  {
    $credentials = $request->only('username', 'password');
    try {
      if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json(['error' => 'invalid_credentials'], 401);
      }
    } catch (JWTException $e) {
      return response()->json(['error' => 'could_not_create_token'], 500);
    }

    return response()->json(compact('token'));
  }

}