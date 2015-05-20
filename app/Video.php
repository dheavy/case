<?php namespace Mypleasure;

use Illuminate\Database\Eloquent\Model;

use Mypleasure\Traits\Slugifies;

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
   * Is the video private (i.e. part of a private collection)?
   *
   * @return boolean  True if private, false, otherwise.
   */
  public function isPrivate()
  {
    return $this->collection->private;
  }

}