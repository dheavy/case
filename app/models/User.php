<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

/**
 * id             {integer}
 * username       {string}
 * password       {string}
 * email          {string}
 * role_id        {integer|foreign:Role}
 * status         {integer}
 * remember_token {string}
 * created_at     {timestamp}
 * updated_at     {timestamp}
 * deleted_at     {timestamp}
 */
class User extends Eloquent implements UserInterface, RemindableInterface {

  use UserTrait, RemindableTrait;

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'users';

  /**
   * The attributes excluded from the model's JSON form.
   *
   * @var array
   */
  protected $hidden = array('password', 'remember_token');

  /**
   * The mass-assignable attributes in this model.
   *
   * @var array
   */
  protected $fillable = array('username', 'password', 'email', 'status', 'role_id');

  /**
   * Relationship with Role model.
   */
  public function role()
  {
    return $this->belongsTo('Role');
  }

}
