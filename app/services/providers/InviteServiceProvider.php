<?php

namespace Mypleasure\Services\Providers;

use Invite;
use InvitesController;
use Illuminate\Support\ServiceProvider;

/**
 * InviteServiceProvider registers all invite-related services.
 * It leverages Laravel's IoC mechanism to deliver the expected
 * dependencies injections in selected classes' instances
 * whenever we invoke them throughout our app.
 */
class InviteServiceProvider extends ServiceProvider {

  public function register()
  {
    $this->app->bind('InvitesController', function($app) {
      return new InvitesController(new Invite);
    });
  }

}