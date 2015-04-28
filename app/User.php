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
   * Start watching UserObserver on model's boot sequence.
   */
  public static function boot()
  {
    parent::boot();
    self::observe(new UserObserver);
  }

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
   * @return boolean  True if successly done, false if failed.
   */
	public function promote()
	{
		$role = Role::where('name', '=', 'admin')->first();

    if ($this->role_id !== $role->id) {
      $this->role_id = $role->id;
      $this->save();
      return true;
    }

    return false;
	}

	/**
   * Demote user to curator role.
   *
   * @return boolean  True if successly done, false if failed.
   */
	public function demote()
	{
		$role = Role::where('name', '=', 'curator')->first();

    if ($this->role_id !== $role->id) {
      $this->role_id = $role->id;
      $this->save();
      return true;
    }

    return false;
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
