<?php namespace Mypleasure\Api\V1\Controllers;

use Mypleasure\User;

class UserController extends BaseController {

  public function index()
  {
    return User::all();
  }

  public function show($id)
  {
    return 'users.show ' . $id;
  }

  public function update($id)
  {
    return 'users.update ' . $id;
  }

  public function destroy($id)
  {
    return 'users.destroy ' . $id;
  }

}