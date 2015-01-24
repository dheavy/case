<?php

/**
 * ProfileController manages displaying views from user's profile.
 */

class ProfileController extends \BaseController {

  /**
   * The current user.
   * @var User
   */
  protected $user;

/**
 * Create instance, assigning currently logged in user to protected variable.
 */
  public function __construct()
  {
    $this->user = Auth::user();
  }

  /**
   * Display user's profile index page.
   *
   * @return Illuminate\View\View
   */
  public function getProfile()
  {
    return View::make('user.profile')->with('user', $this->user);
  }

  /**
   * Display "edit email" form.
   *
   * @return Illuminate\View\View
   */
  public function getEditEmail()
  {
    return View::make('user.editemail')->with('user', $this->user);
  }

  /**
   * Display "edit password" form.
   *
   * @return Illuminate\View\View
   */
  public function getEditPassword()
  {
    return View::make('user.editpassword')->with('user', $this->user);
  }

  /**
   * Display user's list of collections and videos.
   *
   * @return Illuminate\View\View
   */
  public function getVideos()
  {
    return View::make('user.videos')->with('user', $this->user);
  }

}