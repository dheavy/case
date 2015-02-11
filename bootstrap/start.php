<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/
$app = new Illuminate\Foundation\Application;

/*
|--------------------------------------------------------------------------
| Config util
|--------------------------------------------------------------------------
|
| Production config files are loaded first and then merged with overrides
| from other environments. If the production file generates an error
| (e.g. undefined index) the config loading will bail early without loading
| the overrides. This has the undesirable effect of pressing production
| configuration into the local environment.
| The following array, embedding into the $app variable, contains the env
| variables the production environment requires, or provides fallbacks
| in case they do not exist (e.g. in local environments).
|
*/
$config = array();
$config['MEMCACHEDCLOUD_SERVERS_HOST'] = ($_ENV("MEMCACHEDCLOUD_SERVERS") && array_key_exists('host', $_ENV("MEMCACHEDCLOUD_SERVERS"))) ? $_ENV("MEMCACHEDCLOUD_SERVERS")['host'] : '';
$config['MEMCACHEDCLOUD_SERVERS_PORT'] = ($_ENV("MEMCACHEDCLOUD_SERVERS") && array_key_exists('port', $_ENV("MEMCACHEDCLOUD_SERVERS"))) ? (int)$_ENV("MEMCACHEDCLOUD_SERVERS")['port'] : '';
$config['MEMCACHEDCLOUD_USERNAME'] = $_ENV("MEMCACHEDCLOUD_USERNAME") ? $_ENV("MEMCACHEDCLOUD_USERNAME") : '';
$config['MEMCACHEDCLOUD_PASSWORD'] = $_ENV("MEMCACHEDCLOUD_PASSWORD") ? $_ENV("MEMCACHEDCLOUD_PASSWORD") : '';
$config['PSQL_HOST'] = $_ENV("PSQL_HOST") ? $_ENV("PSQL_HOST") : '';
$config['PSQL_DATABASE'] = $_ENV("PSQL_DATABASE") ? $_ENV("PSQL_DATABASE") : '';
$config['PSQL_USERNAME'] = $_ENV("PSQL_USERNAME") ? $_ENV("PSQL_USERNAME") : '';
$config['PSQL_PASSWORD'] = $_ENV("PSQL_PASSWORD") ? $_ENV("PSQL_PASSWORD") : '';
$config['PSQL_PORT'] = $_ENV("PSQL_PORT") ? (int)$_ENV("PSQL_PORT") : '';
$config['MONGODB_HOST'] = $_ENV("MONGODB_HOST") ? $_ENV("MONGODB_HOST") : '';
$config['MONGODB_PORT'] = $_ENV("MONGODB_PORT") ? (int)$_ENV("MONGODB_PORT") : '';
$config['MONGODB_DATABASE'] = $_ENV("MONGODB_DATABASE") ? $_ENV("MONGODB_DATABASE") : '';
$config['MONGODB_USERNAME'] = $_ENV("MONGODB_USERNAME") ? $_ENV("MONGODB_USERNAME") : '';
$config['MONGODB_PASSWORD'] = $_ENV("MONGODB_PASSWORD") ? $_ENV("MONGODB_PASSWORD") : '';
$config['MONGODB_RS'] = $_ENV("MONGODB_RS") ? $_ENV("MONGODB_RS") : '';
$app['_config'] = $config;

/*
|--------------------------------------------------------------------------
| Detect The Application Environment
|--------------------------------------------------------------------------
|
| Laravel takes a dead simple approach to your application environments
| so you can just specify a machine name for the host that matches a
| given environment, then we will automatically detect it for you.
|
*/

$env = $app->detectEnvironment(function() {
  if ($_ENV('LARAVEL_ENV')) {
    return $_ENV('LARAVEL_ENV');
  } else {
    return 'local'; // Default
  }
});

/*
|--------------------------------------------------------------------------
| Bind Paths
|--------------------------------------------------------------------------
|
| Here we are binding the paths configured in paths.php to the app. You
| should not be changing these here. If you need to change these you
| may do so within the paths.php file and they will be bound here.
|
*/

$app->bindInstallPaths(require __DIR__.'/paths.php');

/*
|--------------------------------------------------------------------------
| Load The Application
|--------------------------------------------------------------------------
|
| Here we will load this Illuminate application. We will keep this in a
| separate location so we can isolate the creation of an application
| from the actual running of the application with a given request.
|
*/

$framework = $app['path.base'].
                 '/vendor/laravel/framework/src';

require $framework.'/Illuminate/Foundation/start.php';

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
