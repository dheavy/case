<?php

namespace Mypleasure\Api\V1\Controller;

use Mypleasure\User;
use Mypleasure\Http\Requests\StoreUserRequest;
use Mypleasure\Http\Requests\UpdateUserRequest;
use Mypleasure\Http\Requests\DeleteUserRequest;
use Mypleasure\Api\V1\Transformer\UserTransformer;
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
      return $this->response->error('User ' . $id . ' was not found.');
    }
  }

  public function store(StoreUserRequest $request)
  {
    $user = new User;
    $user->username = $request->input('username');
    $user->password = \Hash::make($request->input('password'));
    $user->save();

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

    // Edge case: if a admin/user attempt to update a record that has been
    // updated by another user prior to this update request.
    if ($user->updated_at > app('request')->get('last_updated')) {
      throw new ConflictHttpException('User was updated prior to your request.');
    }

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

}