<?php namespace Mypleasure\Traits;

trait Slugifies {

  protected $delimiter = '-';

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
    return str_slug($item, $this->delimiter);
  }

}