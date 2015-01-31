<?php

/**
 * The Controller most controllers inherit from in our app.
 */

class BaseController extends Controller {

  /**
   * Setup the layout used by the controller.
   *
   * @return void
   */
  protected function setupLayout()
  {
    if ( ! is_null($this->layout))
    {
      $this->layout = View::make($this->layout);
    }
  }

  /**
   * Slugifies the text passed as argument.
   *
   * @param  string $text
   * @return string A slug based on the text.
   */
  protected function slugify($text)
  {
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    if (empty($text)) return 'n-a';

    return $text;
  }

}
