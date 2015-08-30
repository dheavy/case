<?php

namespace Mypleasure\Api\V1\Controller;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mypleasure\Api\V1\Exception\CreateTokenFailedException;
use Mypleasure\Api\V1\Exception\InvalidCredentialsException;
use Illuminate\Http\Request;

class AuthController extends BaseController {

  public function authenticate(Request $request)
  {
    $credentials = $request->only('username', 'password');
    try {
      if (!$token = JWTAuth::attempt($credentials)) {
        throw new InvalidCredentialsException;
      }
    } catch (JWTException $e) {
      throw new CreateTokenFailedException('Could not create token.');
    }

    return response()->json(compact('token'));
  }

}