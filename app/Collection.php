<?php namespace Mypleasure;

use Illuminate\Database\Eloquent\Model;
use Mypleasure\Traits\Slugifies;

/**
 * id         {integer}
 * name       {string}
 * slug       {string}
 * private    {boolean}
 * user_id    {integer|User:id}
 * created_at {timestamp}
 * updated_at {timestamp}
 */
class Collection extends Model {

  use Slugifies;

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
    return $this->belongsTo('Mypleasure\User');
  }

  /**
   * Relation with Video model.
   *
   * @return Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function videos()
  {
    return $this->hasMany('Mypleasure\Video');
  }

  /**
   * Is this collection the user's default one.
   *
   * @return boolean True if it is, false otherwise.
   */
  public function isDefault()
  {
    return $this->id === $this->user->collections()->first()->id;
  }

}