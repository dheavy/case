<?php

/**
 * id            {integer}
 * hash          {string}
 * title         {string}
 * slug          {string}
 * poster        {string}
 * method        {string}
 * original_url  {string}
 * embed_url     {string}
 * duration      {string}
 * active        {integer}
 * user_id       {integer}
 * created_at    {timestamp}
 * updated_at    {timestamp}
 */

class Video extends Eloquent {

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'videos';

  /**
   * The mass-assignable attributes in this model.
   *
   * @var array
   */
  protected $fillable = array('hash', 'title', 'slug', 'poster', 'method', 'original_url', 'embed_url', 'active', 'duration');

  /**
   * Relation with Collection model.
   *
   * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function collections()
  {
    return $this->belongsToMany('Collection', 'collection_video')->withTimestamps();
  }

  /**
   * Relation with Tag model.
   *
   * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function tags()
  {
    return $this->belongsToMany('Tag', 'tag_video')->withTimestamps();
  }

  /**
   * Is the video publicly visible (i.e. part of a publicly visible collection)?
   *
   * @return boolean True if visible, false, otherwise.
   */
  public function isPublic()
  {
    return $this->collections->first()->isPublic();
  }

}