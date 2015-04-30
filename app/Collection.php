<?php namespace Mypleasure;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model {

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'collections';

   /**
   * The mass-assignable attributes in this model.
   *
   * @var array
   */
  protected $fillable = ['name', 'status'];

  /**
   * Relation with User model.
   *
   * @return Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function user()
  {
    return $this->belongsTo('User');
  }

  /**
   * Relation with Video model.
   *
   * @return Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function videos()
  {
    return $this->belongsToMany('Video', 'collection_video');
  }

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