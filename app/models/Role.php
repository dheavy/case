<?php

/**
 * id         {integer}
 * name       {string}
 * created_at {timestamp}
 * updated_at {timestamp}
 */

class Role extends Eloquent {

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'roles';

  /**
   * The mass-assignable attributes in this model.
   *
   * @var array
   */
  protected $fillable = array('name');

  /**
   * Relation with User model.
   *
   * @return  Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function users()
  {
    return $this->hasMany('User', 'role_id')->withTimestamps();
  }

}
