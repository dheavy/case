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
   * Delete all videos attach to this collection, then delete the collection.
   *
   * @return boolean True if no error occured.
   */
  public function dispose()
  {
    $this->videos->each(function($video) {
      $video->delete();
    });
    $this->delete();

    return true;
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

}