<?php

namespace Mypleasure\Api\V1\Controller;

use Mypleasure\User;
use Mypleasure\Http\Requests\StoreUserRequest;
use Mypleasure\Http\Requests\UpdateUserRequest;
use Mypleasure\Api\V1\Transformer\UserTransformer;

class UserController extends BaseController {

  public function __construct()
  {
    $this->middleware('api.auth', ['only' => ['update', 'delete']]);
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
    $user = \JWTAuth::parseToken()->toUser();

    $username = $request->input('username');
    $currentPassword = $request->input('current_password');
    $newPassword = $request->input('password');
    $newPasswordConfirm = $request->input('password_confirmation');
    $email = $request->input('current_password');

    if ($username) {
      return $this->response->errorForbidden('Username cannot be changed.');
    }

    if ($currentPassword !== null && $newPassword !== null && $newPasswordConfirm !== null) {
      if (\Hash::check($currentPassword, $user->password)) {
        $user->password = \Hash::make($newPassword);
        $user->save();
        return response()->json(['status_code' => 200, 'message' => 'Password successfully modified.']);
      } else {
        return $this->response->errorBadRequest('Invalid password.');
      }
    }

    if ($email) {

    }

    return $this->response->errorBadRequest();
  }

  public function destroy($id)
  {
    return 'users.destroy ' . $id;
  }

}