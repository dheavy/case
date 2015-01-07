<?php

use GrahamCampbell\Throttle\Facades\Throttle;

class UsersController extends \BaseController {

  protected $user;

  public function __construct(User $user)
  {
    $this->user = $user;
  }

  public function index()
  {

  }

  public function store()
  {

  }

  public function show($id)
  {

  }

  public function destroy($id)
  {

  }

  public function updatePassword()
  {

  }

  public function updateEmail()
  {

  }

}
