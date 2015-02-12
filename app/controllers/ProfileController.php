<?php

/**
 * ProfileController manages displaying views from user's profile.
 */

class ProfileController extends \BaseController {

  /**
   * An instance of the User model passed via injection, to loosen dependencies and allow easier testing.
   *
   * @var User
   */
  protected $user;

/**
 * Create instance, assigning currently logged in user to protected variable.
 */
  public function __construct()
  {
    parent::__construct();
    $this->user = Auth::user();
  }

  /**
   * Display user's profile index page.
   *
   * @return Illuminate\View\View
   */
  public function getProfile()
  {
    return View::make('users.profile')->with('user', $this->user);
  }

  /**
   * Display "edit email" form.
   *
   * @return Illuminate\View\View
   */
  public function getEditEmail()
  {
    return View::make('users.email')->with('user', $this->user);
  }

  /**
   * Display "edit password" form.
   *
   * @return Illuminate\View\View
   */
  public function getEditPassword()
  {
    return View::make('users.password')->with('user', $this->user);
  }

  /**
   * Display "add video" form.
   *
   * @return Illuminate\View\View
   */
  public function getAddVideo()
  {
    return View::make('videos.create')->with('user', $this->user);
  }

  /**
   * Display "add fake video" page.
   *
   * @return Illuminate\View\View
   */
  public function getAddVideoDebug()
  {
    return View::make('debug.addvideo')->with('user', $this->user);
  }

  /**
   * Display "delete account" form
   *
   * @return Illuminate\View\View
   */
  public function getDelete()
  {
    return View::make('users.delete')->with('user', $this->user);
  }

}