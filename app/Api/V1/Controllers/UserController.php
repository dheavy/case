<?php namespace Mypleasure\Api\V1\Controllers;

use Mypleasure\User;
use Illuminate\Http\Request;
use Mypleasure\Api\V1\Transformers\UserTransformer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends BaseController {

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

  public function store(Request $request, $id)
  {

  }

  public function update(Request $request, $id)
  {
    return 'users.update ' . $id;
  }

  public function destroy($id)
  {
    return 'users.destroy ' . $id;
  }

}