<?php namespace Mypleasure\Observers;

use Mypleasure\User;
use Mypleasure\Role;
use Mypleasure\Collection;

/**
 * UserObserver leverages observers in Laravel to allow
 * processing model level events on the User model.
 */

class UserObserver {

  /**
   * Before saving model, set a default email if none was given.
   *
   * @param Mypleasure\User  The user model about to be saved.
   */
  public function saving(User $user)
  {
    if (!$user->email || $user->email === '') {
      $user->email = md5($user->username) . User::$EMAIL_PLACEHOLDER_SUFFIX;
    }
  }

  /**
   * After saving a model, create default collection if user has none yet,
   * and set default status (NOT admin by default).
   *
   * @param Mypleasure\User  The user model saved.
   */
  public function saved(User $user)
  {
    if ($user->collections()->count() == 0) {
      $collection = Collection::create([
        'name' => $user->username
      ]);
      $user->collections()->save($collection);
    }

    if ($user->admin == null) {
      $user->admin = false;
    }
  }

}
