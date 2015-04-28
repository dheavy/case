<?php namespace Mypleasure;

use Illuminate\Database\Eloquent\Model;

/**
 * id         {integer}
 * name       {string}
 * created_at {timestamp}
 * updated_at {timestamp}
 */
class Role extends Model {

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'roles';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['name'];

  /**
   * Relation with User model.
   *
   * @return  Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function users()
  {
    return $this->hasMany('\Mypleasure\User');
  }

}