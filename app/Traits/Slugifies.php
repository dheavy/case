<?php namespace Mypleasure\Traits;

trait Slugifies {

  /**
   * Make slug from the instance's name.
   *
   * @return string  The resulting slug.
   */
  public function slugifyName()
  {
    $this->slug = $this->slugify($this->name);
    return $this->slug;
  }

  /**
   * Make slug from the instance's title.
   *
   * @return string  The resulting slug.
   */
  public function slugifyTitle()
  {
    $this->slug = $this->slugify($this->title);
    return $this->slug;
  }

  /**
   * Slugify item passed as argument.
   *
   * @param  string  $text
   * @return string  A slug based on the text.
   */
  protected function slugify($item)
  {
    $item = preg_replace('~[^\\pL\d]+~u', '-', $item);
    $item = trim($item, '-');
    $item = iconv('utf-8', 'us-ascii//TRANSLIT', $item);
    $item = strtolower($item);
    $item = preg_replace('~[^-\w]+~', '', $item);
    if (empty($item)) return 'n-a';

    return $item;
  }

}