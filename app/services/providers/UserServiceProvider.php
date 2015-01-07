<?php

namespace Mypleasure\Services\Providers;

use UsersController;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Mypleasure\Services\User\UserAuthValidator;
use Mypleasure\Services\User\UserCreateValidator;
use Mypleasure\Services\User\UserUpdateEmailValidator;
use Mypleasure\Services\User\UserUpdatePasswordValidator;

/**
 * UserServiceProvider registers all users-related services.
 * It leverages Laravel's IoC mechanism to deliver the expected
 * dependencies injections in selected classes' instances
 * whenever we invoke them throughout our app.
 */

class UserServiceProvider extends ServiceProvider {

  public function register()
  {
    $this->app->bind('UserAuthValidator', function($app) {
      return new UserAuthValidator(Validator::getFacadeRoot());
    });

    $this->app->bind('UserCreateValidator', function($app) {
      return new UserCreateValidator(Validator::getFacadeRoot());
    });

    $this->app->bind('UserUpdateEmailValidator', function($app) {
      return new UserUpdateEmailValidator(Validator::getFacadeRoot());
    });

    $this->app->bind('UserUpdatePasswordValidator', function($app) {
      return new UserUpdatePasswordValidator(Validator::getFacadeRoot());
    });

    $this->app->bind('UsersController', function($app) {
      return new UsersController(
        $app->make('UserCreateValidator'),
        $app->make('UserAuthValidator'),
        $app->make('UserUpdateEmailValidator'),
        $app->make('UserUpdatePasswordValidator')
      );
    });
  }

}
