<?php namespace Mypleasure;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Mypleasure\Observers\UserObserver;

/**
 * id             {integer}
 * username       {string}
 * password       {string}
 * email          {string}
 * role_id        {integer|foreign:Role}
 * remember_token {string}
 * created_at     {timestamp}
 * updated_at     {timestamp}
 * deleted_at     {timestamp}
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword, SoftDeletes;

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
	protected $fillable = ['username', 'email', 'password'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	/**
	 * Set the user's session to naughty mode.
	 *
	 * @param boolean $naughty  True to set to naughty mode, false to unset.
	 */
	public function setNaughtyMode($naughty)
	{
		Session::put('naughty', $naughty);
	}

	/**
   * Promote user to admin role.
   *
   * @return boolean  True if successfully set to admin, false otherwise.
   */
	public function promote()
	{
		$admin = Role::where('name', '=', 'admin')->first();

    if ($this->role->id == $admin->id) return true;
    if ($this->role()->associate($admin)) return true;

    return false;
	}

	/**
   * Demote user to curator role.
   *
   * @return boolean  True if successfully set to curator, false otherwise.
   */
	public function demote()
	{
		$curator = Role::where('name', '=', 'curator')->first();

    if ($this->role->id == $curator->id) return true;
    if ($this->role()->associate($curator)) return true;

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
   * Relation with Role model.
   *
   * @return Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function role()
  {
    return $this->belongsTo('\Mypleasure\Role', 'role_id', 'id');
  }

}
