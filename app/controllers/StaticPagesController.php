<?php

/**
 * StaticPagesController deals with rendering pages that are public and static.
 */

class StaticPagesController extends \BaseController {

  /**
   * Renders Homepage view.
   *
   * @return Illuminate\View\View
   */
  public function getHome()
  {
    return View::make('static.home');
  }

}
