<?php namespace Mypleasure\Observers;

use Mypleasure\User;
use Mypleasure\Role;

/**
 * UserObserver leverages observers in Laravel to allow
 * processing model level events on the User model.
 */

class UserObserver {

  /**
   * Before saving model, set a default email if none was given.
   *
   * @param User  The user model about to be saved.
   */
  public function saving(User $user)
  {
    if (!$user->email || $user->email === '') {
      $user->email = md5($user->username) . User::$EMAIL_PLACEHOLDER_SUFFIX;
    }
  }

  /**
   * After saving a model, set role by default to "curator" if none was given.
   *
   * @param User  The user model saved.
   */
  public function saved(User $user)
  {
    if ($user->role_id == null) {
      $role = Role::where('name', '=', 'curator')->first();
      $role->users()->save($user);
    }
  }

}
