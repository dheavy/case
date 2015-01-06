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
   * Relationship with User model.
   */
  public function users()
  {
    return $this->hasMany('User', 'role_id');
  }

}
