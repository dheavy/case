<?php

namespace Mypleasure\Traits;

use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use Dingo\Api\Exception\ValidationHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;

trait ResetsPasswords {

  /**
   * Send a reset link to the given user.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function postEmail(Request $request)
  {
    $validator = \Validator::make($request->only('email'), ['email' => 'required|email']);
    if ($validator->fails()) {
      throw new ValidationHttpException($validator->errors());
    }

    $response = Password::sendResetLink($request->only('email'), function (Message $message) {
        $message->subject($this->getEmailSubject());
    });

    switch ($response) {
      case Password::RESET_LINK_SENT:
        return response()->json(['status_code' => 200, 'message' => trans($response)], 200);

      case Password::INVALID_USER:
        return response()->json(['status_code' => 400, 'message' => 'User is invalid.', 'errors' => ['email' => trans($response)]], 400);
    }
  }

  /**
  * Get the e-mail subject line to be used for the reset link email.
  *
  * @return string
  */
  protected function getEmailSubject()
  {
    return isset($this->subject) ? $this->subject : 'Your Password Reset Link';
  }

  /**
   * Provide the given token for password reset, to be used
   * when displaying the reset view for this given token.
   *
   * @param  string  $token
   * @return \Illuminate\Http\Response
   */
  public function getReset($token = null)
  {
    if (is_null($token)) {
      throw new NotFoundHttpException;
    }

    return response()->json(['status_code' => 200, 'message' => 'Token provided.', 'token' => $token], 200);
  }

  /**
   * Reset the given user's password.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function postReset(Request $request)
  {
    $validator = \Validator::make($request->only(
      'token', 'email', 'password', 'password_confirmation'
    ), [
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|confirmed|min:6',
    ]);

    if ($validator->fails()) {
      throw new ValidationHttpException($validator->errors());
    }

    $credentials = $request->only(
      'email', 'password', 'password_confirmation', 'token'
    );

    $response = Password::reset($credentials, function ($user, $password) {
      $this->resetPassword($user, $password);
    });

    switch ($response) {
      case Password::PASSWORD_RESET:
        return response()->json(['status_code' => 200, 'message' => 'Password successfully reset.'], 200);

      default:
        return response()->json(['status_code' => 400, 'message' => 'Could not reset password.', 'errors' => ['email' => trans($response)]]);
    }
  }

  /**
   * Reset the given user's password.
   *
   * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
   * @param  string  $password
   * @return void
   */
  protected function resetPassword($user, $password)
  {
      $user->password = bcrypt($password);
      $user->save();
  }

}