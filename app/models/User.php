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
   * The suffix used when crafting a dummy email.
   *
   * @const string
   */
  public static $EMAIL_PLACEHOLDER_SUFFIX = '.no.email.provided@mypleasu.re';

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
   * Start watching UserObserver on model's boot sequence.
   */
  public static function boot()
  {
    parent::boot();
    self::observe(new UserObserver);
  }

  /**
   * Relationship with Role model.
   *
   * @return Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function role()
  {
    return $this->belongsTo('Role');
  }

  /**
   * Whether user has a placeholder email.
   *
   * @return boolean
   */
  public function hasPlaceholderEmail()
  {
    return stripos($email, self::$EMAIL_PLACEHOLDER_SUFFIX);
  }

}
