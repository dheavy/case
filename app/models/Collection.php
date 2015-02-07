<?php

/**
 * id         {integer}
 * name       {string}
 * slug       {string}
 * user_id    {integer}
 * created_at {timestamp}
 * updated_at {timestamp}
 */

class Collection extends Eloquent {

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
  protected $fillable = array('name', 'slug', 'status', 'user_id');

  /**
   * Relation with User model.
   *
   * @return Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function user()
  {
    return $this->belongsTo('User')->withTimestamps();
  }

  /**
   * Relation with Video model.
   *
   * @return Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function videos()
  {
    return $this->belongsToMany('Video', 'collection_video')->withTimestamps();
  }

  /**
   * Is the collection public?
   *
   * @return boolean True if it is, false otherwise.
   */
  public function isPublic()
  {
    return $this->status === 1;
  }

  /**
   * Is the collection private?
   *
   * @return boolean True if it is, false otherwise.
   */
  public function isPrivate()
  {
    return $this->status === 0;
  }

}