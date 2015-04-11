<?php

return array(

  /*
  |--------------------------------------------------------------------------
  | PDO Fetch Style
  |--------------------------------------------------------------------------
  |
  | By default, database results will be returned as instances of the PHP
  | stdClass object; however, you may desire to retrieve records in an
  | array format for simplicity. Here you can tweak the fetch style.
  |
  */

  'fetch' => PDO::FETCH_CLASS,

  /*
  |--------------------------------------------------------------------------
  | Default Database Connection Name
  |--------------------------------------------------------------------------
  |
  | Here you may specify which of the database connections below you wish
  | to use as your default connection for all database work. Of course
  | you may use many connections at once using the Database library.
  |
  */

  'default' => 'pgsql',

  /*
  |--------------------------------------------------------------------------
  | Database Connections
  |--------------------------------------------------------------------------
  |
  | Here are each of the database connections setup for your application.
  | Of course, examples of configuring each database platform that is
  | supported by Laravel is shown below to make development simple.
  |
  |
  | All database work in Laravel is done through the PHP PDO facilities
  | so make sure you have the driver for your particular database of
  | choice installed on your machine before you begin development.
  |
  */
  'connections' => array(

    'pgsql' => array(
      'driver'   => 'pgsql',
      'host'     => App::getFacadeRoot()->_config['PSQL_HOST'],
      'database' => App::getFacadeRoot()->_config['PSQL_DATABASE'],
      'username' => App::getFacadeRoot()->_config['PSQL_USERNAME'],
      'password' => App::getFacadeRoot()->_config['PSQL_PASSWORD'],
      'charset'  => 'utf8',
      'prefix'   => '',
      'port'     => App::getFacadeRoot()->_config['PSQL_PORT'],
      'schema'   => 'public',
    ),

    'mongodb' => array(
      'driver'   => 'mongodb',
      'host'     => App::getFacadeRoot()->_config['MONGODB_HOST'],
      'port'     => App::getFacadeRoot()->_config['MONGODB_PORT'],
      'username' => App::getFacadeRoot()->_config['MONGODB_USERNAME'],
      'password' => App::getFacadeRoot()->_config['MONGODB_PASSWORD'],
      'database' => App::getFacadeRoot()->_config['MONGODB_DATABASE'],
      'options'  => array('replicaSet' => App::getFacadeRoot()->_config['MONGODB_RS'])
    ),

  ),

  /*
  |--------------------------------------------------------------------------
  | Migration Repository Table
  |--------------------------------------------------------------------------
  |
  | This table keeps track of all the migrations that have already run for
  | your application. Using this information, we can determine which of
  | the migrations on disk haven't actually been run in the database.
  |
  */

  'migrations' => 'migrations',

  /*
  |--------------------------------------------------------------------------
  | Redis Databases
  |--------------------------------------------------------------------------
  |
  | Redis is an open source, fast, and advanced key-value store that also
  | provides a richer set of commands than a typical key-value systems
  | such as APC or Memcached. Laravel makes it easy to dig right in.
  |
  */

  'redis' => array(

    'cluster' => false,

    'default' => array(
      'host'     => App::getFacadeRoot()->_config['REDISTOGO_URL_HOST'],
      'port'     => App::getFacadeRoot()->_config['REDISTOGO_URL_PORT'],
      'database' => 0,
      'password' => App::getFacadeRoot()->_config['REDISTOGO_URL_PASS']
    ),

  ),

);
