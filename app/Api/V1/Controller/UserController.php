<?php

namespace Mypleasure\Api\V1\Controller;

use Mypleasure\User;
use Mypleasure\Http\Requests\StoreUserRequest;
use Mypleasure\Http\Requests\UpdateUserRequest;
use Mypleasure\Api\V1\Transformer\UserTransformer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
      return $this->item($user->first(), new UserTransformer);
    } else {
      throw new NotFoundHttpException('User ' . $id . ' was not found.');
    }
  }

  public function store(StoreUserRequest $request)
  {
    $user = new User;
    $user->username = $request->input('username');
    $user->password = \Hash::make($request->input('password'));
    $user->save();

    return $this->item($user, new UserTransformer);
  }

  public function update(UpdateUserRequest $request, $id)
  {
    $currentPassword = $request->input('current_password');
    $email = $request->input('current_password');

    if ($currentPassword) {

    }

    if ($email) {

    }
  }

  public function destroy($id)
  {
    return 'users.destroy ' . $id;
  }

}