<?php

namespace Mypleasure\Services\Providers;

use User;
use Video;
use Config;
use Collection;
use AuthController;
use UsersController;
use VideosController;
use CollectionsController;
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

    $this->app->bind('CollectionsController', function($app) {
      return new CollectionsController(new Collection);
    });

    $this->app->bind('VideosController', function($app) {
      return new VideosController(new Video);
    });

    $this->app->bind('UsersController', function($app) {
      return new UsersController(
        new User,
        array(
          'create' => $app->make('UserCreateValidator'),
          'updateEmail' => $app->make('UserUpdateEmailValidator'),
          'updatePassword' => $app->make('UserUpdatePasswordValidator')
        ),
        array(
          'collection' => $app->make('CollectionsController'),
          'video' => $app->make('VideosController')
        )
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
