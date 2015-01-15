<?php

class StaticPagesController extends \BaseController {

  public function getHome()
  {
    return View::make('static.home');
  }

}
