<?php

namespace Mypleasure\Api\V1\Controller;

use Mypleasure\User;
use Mypleasure\Invite;
use Mypleasure\Http\Requests\StoreUserRequest;
use Mypleasure\Http\Requests\UpdateUserRequest;
use Mypleasure\Http\Requests\DeleteUserRequest;
use Mypleasure\Http\Requests\CreateUserFromInviteRequest;
use Mypleasure\Api\V1\Transformer\UserTransformer;
use Mypleasure\Api\V1\Controller\AuthController;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UserController extends BaseController {

  public function __construct()
  {
    $this->middleware('api.auth', ['only' => ['index', 'update', 'delete']]);
  }

  public function index()
  {
    return $this->collection(User::all(), new UserTransformer);
  }

  public function show($id)
  {
    $user = User::find($id);
    if ($user) {
      return $this->response->item($user->first(), new UserTransformer);
    } else {
      return $this->response->errorNotFound('User ' . $id . ' was not found.');
    }
  }

  public function store(StoreUserRequest $request)
  {
    $user = $this->createUser(
      $request->input('username'),
      $request->input('email', ''),
      $request->input('password')
    );

    return $this->response->item($user, new UserTransformer);
  }

  public function update(UpdateUserRequest $request, $id)
  {
    // From JWT get user. From request get possible input.
    $user = \JWTAuth::parseToken()->toUser();
    $username = $request->input('username');
    $currentPassword = $request->input('current_password');
    $newPassword = $request->input('password');
    $newPasswordConfirm = $request->input('password_confirmation');
    $email = $request->input('email');

    // Forbid editing username.
    if ($username) {
      return $this->response->errorForbidden('Username cannot be changed.');
    }

    // If current password and a new pair of candidate passwords are provided,
    // attempt editing the user's password.
    if ($currentPassword !== null && $newPassword !== null && $newPasswordConfirm !== null) {
      if (\Hash::check($currentPassword, $user->password)) {
        $user->password = \Hash::make($newPassword);
        $user->save();
        return response()->json(['status_code' => 200, 'message' => 'Password successfully modified.'], 200);
      } else {
        throw new UpdateResourceFailedException('Could not update password.', $validator->errors());
      }
    }

    // If email is not null (i.e. filled, even with whitespaces),
    // attempt editing the user's email address.
    if ($email !== null) {
      // User removed her email.
      if (trim($email) == '') {
        $user->email = md5($user->username) . User::$EMAIL_PLACEHOLDER_SUFFIX;
        $user->save();
        return response()->json(['status_code' => 200, 'message' => 'Email address successfully removed.'], 200);
      } else {
        // New email: attempt validation.
        $validator = \Validator::make(['email' => $email], [
          'email' => 'email|unique:users'
        ]);

        if ($validator->fails()) {
          throw new UpdateResourceFailedException('Could not update email address.', $validator->errors());
        }

        $user->email = $email;
        $user->save();

        return response()->json(['status_code' => 200, 'message' => 'Email address successfully modified.'], 200);
      }
    }

    throw new UpdateResourceFailedException('Could not update email address.');
  }

  public function destroy(DeleteUserRequest $request, $id)
  {
    $user = User::find($id);
    $user->delete();
    return response()->json(['status_code' => 200, 'message' => 'User ' . $user->username . ' (id: ' . $user->id . ') was permanently deleted.'], 200);
  }

  /**
   * Return a payload containing information from a claimed invite.
   * Should be used to set up onboarding from this invite,
   * i.e. present to invitee a form where she will create a new User,
   * then save the User (via UserController#createFromInvite) and proceed
   * with onboarding apparatus (maybe send an email, etc...)
   *
   * @param  Invite $invite
   * @return Response
   */
  public function onboardFromInvite(Invite $invite)
  {
    return response()->json([
      'status_code' => 200,
      'message' => 'Onboard from invite.',
      'invite' => [
        'email' => $invite->email,
        'code' => $invite->code
      ]
    ], 200);
  }

  /**
   * Create new User from an Invite onboarding process.
   * Log in the newly created user. Returned JSON payload
   * includes the token.
   *
   * @param  CreateUserFromInviteRequest $request
   * @param  AuthController              $authController
   * @return Response
   */
  public function createFromInvite(CreateUserFromInviteRequest $request, AuthController $authController)
  {
    $invite = Invite::where('code', $request->input('code'))
                    ->where('email', $request->input('email'))
                    ->first();

    if (!$invite) {
      return response()->json([
        'status_code' => 404,
        'message' => 'Invite not found.'
      ], 404);
    }

    if ($invite->claimed_at == null) {
      return response()->json([
        'status_code' => 401,
        'message' => 'Invite not yet claimed.'
      ], 401);
    }

    $user = $this->createUser(
      $request->input('username'),
      $request->input('email'),
      $request->input('password')
    );

    $token = $authController->createToken([
      'username' => $request->input('username'),
      'password' => $request->input('password')
    ]);

    return response()->json([
      'status_code' => 200,
      'message' => 'Token created.',
      'token' => $token
    ], 200);
  }

  protected function createUser($username, $email, $password)
  {
    $user = new User;
    $user->username = $username;
    $user->password = \Hash::make($password);
    $user->email = $email;
    $user->save();
    return $user;
  }

}