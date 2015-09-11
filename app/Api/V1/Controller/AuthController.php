<?php

namespace Mypleasure\Api\V1\Controller;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mypleasure\Api\V1\Exception\CreateTokenFailedException;
use Mypleasure\Api\V1\Exception\InvalidCredentialsException;
use Illuminate\Http\Request;

class AuthController extends BaseController {

  public function __construct()
  {
    $this->middleware('jwt.refresh', ['only' => ['invalidate']]);
  }

  public function authenticate(Request $request)
  {
    $credentials = $request->only('username', 'password');

    $token = $this->createToken($credentials);

    return response()->json([
      'status_code' => 200,
      'message' => 'Token created.',
      'token' => $token
    ], 200);
  }

  public function invalidate(Request $request)
  {
    JWTAuth::invalidate(JWTAuth::getToken());
    return response()->json(['status_code' => 200, 'message' => 'User logged out.'], 200);
  }

  public function createToken($credentials)
  {
    try {
      if (!$token = JWTAuth::attempt($credentials)) {
        throw new InvalidCredentialsException;
      }
    } catch (JWTException $e) {
      throw new CreateTokenFailedException('Could not create token.');
    }

    return $token;
  }

}