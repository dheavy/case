<?php

use Carbon\Carbon;

/**
 * id         {integer}
 * name       {string}
 * created_at {timestamp}
 * updated_at {timestamp}
 */

class Tag extends Eloquent {

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'tags';

  /**
   * The mass-assignable attribute in this model.
   *
   * @var array
   */
  protected $fillable = array('name');

  /**
   * Start watching TagObserver on model's boot sequence.
   */
  public static function boot()
  {
    parent::boot();
    self::observe(new TagObserver);
  }

  public function fetchOrCreate($name)
  {
    $tag = Tag::where('name', '=', $name)->first();
    if ($tag) return $tag;

    $now = Carbon::now()->toDateTimeString();
    $tag = new Tag;
    $tag->name = $name;
    $tag->save();

    return $tag;
  }

  /**
   * Relation with Video model.
   *
   * @return Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function videos()
  {
    return $this->belongsToMany('Videos', 'tag_video')->withTimestamps();
  }

}