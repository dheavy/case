<?php

namespace Mypleasure;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * id             {integer}
 * username       {string}
 * password       {string}
 * email          {string}
 * admin          {boolean}
 * remember_token {string}
 * created_at     {timestamp}
 * updated_at     {timestamp}
 * deleted_at     {timestamp}
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Promote user to admin role.
     *
     * @return boolean  True if successfully set to admin, false otherwise.
     */
    public function promote()
    {
      if (!$this->admin) {
        $this->admin = true;
        $this->save();
        return true;
      }

      return false;
    }

    /**
     * Demote user to curator role.
     *
     * @return boolean  True if successfully unset admin, false otherwise.
     */
    public function demote()
    {
      if ($this->admin) {
        $this->admin = false;
        $this->save();
        return true;
      }

      return false;
    }

  /**
   * Whether user has a placeholder email.
   *
   * @return boolean
   */
  public function hasPlaceholderEmail()
  {
    return (bool)stripos($this->email, self::$EMAIL_PLACEHOLDER_SUFFIX);
  }

  /**
   * Relation with Collection model.
   *
   * @return Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function collections()
  {
    return $this->hasMany('\Mypleasure\Collection');
  }

  /**
   * Relation with Video model. User has many Videos through Collections.
   *
   * @return Illuminate\Database\Eloquent\Relations\HasManyThrough
   */
  public function videos()
  {
    return $this->hasManyThrough('\Mypleasure\Video', '\Mypleasure\Collection');
  }

  /**
   * Relation with Invite model. User has many Invites.
   *
   * @return Illuminate\Database\Eloquent\Relations\HasManyThrough
   */
  public function invites()
  {
    return $this->hasMany('\Mypleasure\Invite', 'from_id');
  }
}
