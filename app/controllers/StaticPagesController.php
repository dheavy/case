<?php

/**
 * StaticPagesController deals with rendering pages that are public and static.
 */

class StaticPagesController extends \BaseController {

  /**
   * Create instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Renders Homepage view.
   * GET /
   *
   * @return Illuminate\View\View
   */
  public function getHome()
  {
    if (Auth::check()) {
      $user = Auth::user();
      return View::make('users.profile')->with('user', $user);
    }
    return View::make('static.home');
  }

  public function getExtensionClose()
  {
    return View::make('extension.close');
  }

}
