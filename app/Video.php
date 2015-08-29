<?php namespace Mypleasure;

use Illuminate\Database\Eloquent\Model;

use Mypleasure\Traits\Slugifies;

/**
 * id            {integer}
 * hash          {string}
 * collection_id {integer}
 * title         {string}
 * slug          {string}
 * poster        {string}
 * original_url  {string}
 * embed_url     {string}
 * duration      {string}
 * naughty       {boolean}
 * created_at    {timestamp}
 * updated_at    {timestamp}
 */
class Video extends Model {

  use Slugifies;

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
  protected $fillable = ['hash', 'collection_id', 'title', 'slug', 'poster', 'original_url', 'embed_url', 'duration', 'naughty'];

  /**
   * Relation with Collection model.
   *
   * @return Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function collection()
  {
    return $this->belongsTo('Mypleasure\Collection');
  }

  /**
   * Relation with Tag model.
   *
   * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function videos()
  {
    return $this->belongsToMany('Mypleasure\Tag')->withTimestamps();
  }

  /**
   * Is the video private (i.e. part of a private collection)?
   *
   * @return boolean  True if private, false, otherwise.
   */
  public function isPrivate()
  {
    return $this->collection->private;
  }

}