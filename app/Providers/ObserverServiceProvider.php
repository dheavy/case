<?php namespace Mypleasure\Providers;

use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{

  /**
   * Bootstrap any necessary services.
   *
   * @return void
   */
  public function boot()
  {
    \Mypleasure\User::observe(new \Mypleasure\Observers\UserObserver);
    \Mypleasure\Collection::observe(new \Mypleasure\Observers\CollectionObserver);
    \Mypleasure\Video::observe(new \Mypleasure\Observers\VideoObserver);
  }

  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register()
  {
  }

}