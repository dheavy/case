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
    return View::make('static.home');
  }

}
