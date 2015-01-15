<?php

namespace Mypleasure\Services\Providers;

use User;
use Config;
use AuthController;
use UsersController;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Mypleasure\Services\Validation\User\UserAuthValidator;
use Mypleasure\Services\Validation\User\UserCreateValidator;
use Mypleasure\Services\Validation\User\UserUpdateEmailValidator;
use Mypleasure\Services\Validation\User\UserUpdatePasswordValidator;

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
        new User,
        $app->make('UserCreateValidator'),
        $app->make('UserUpdateEmailValidator'),
        $app->make('UserUpdatePasswordValidator')
      );
    });

    $this->app->bind('AuthController', function($app) {
      return new AuthController(
        $app->make('UserAuthValidator'),
        Config::get('app.throttling_max_attempts'),
        Config::get('app.throttling_retention_time')
      );
    });
  }

}
