<?php namespace Mypleasure;

use Illuminate\Database\Eloquent\Model;
use Mypleasure\Traits\Slugifies;

/**
 * id         {integer}
 * name       {string}
 * slug       {string}
 * created_at {timestamp}
 * updated_at {timestamp}
 */
class Tag extends Model {

  use Slugifies;

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'tags';

  /**
   * The mass-assignable attributes in this model.
   *
   * @var array
   */
  protected $fillable = ['name', 'slug'];

  /**
   * Relation with Video model.
   *
   * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function videos()
  {
    return $this->belongsToMany('Mypleasure\Video')->withTimestamps();
  }

}
