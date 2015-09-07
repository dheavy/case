<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::controllers([
	//'auth'     => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

  // Auth
  $api->post('login',          ['as' => 'auth.login',    'uses' => 'Mypleasure\Api\V1\Controller\AuthController@authenticate']);
  $api->get('logout',          ['as' => 'auth.logout',   'uses' => 'Mypleasure\Api\V1\Controller\AuthController@invalidate']);

  // Password
  $api->post('password/email',         ['as' => 'password.email',      'uses' => 'Mypleasure\Api\V1\Controller\PasswordController@postEmail']);
  $api->get('password/reset/{token}',  ['as' => 'password.getreset',  'uses' => 'Mypleasure\Api\V1\Controller\PasswordController@getReset']);
  $api->post('password/reset',         ['as' => 'password.postreset', 'uses' => 'Mypleasure\Api\V1\Controller\PasswordController@postReset']);

  // User
  $api->get('users',           ['as' => 'users.index',   'uses' => 'Mypleasure\Api\V1\Controller\UserController@index']);
  $api->get('users/{id}',      ['as' => 'users.show',    'uses' => 'Mypleasure\Api\V1\Controller\UserController@show']);
  $api->post('users',          ['as' => 'users.store',   'uses' => 'Mypleasure\Api\V1\Controller\UserController@store']);
  $api->put('users/{id}',      ['as' => 'users.update',  'uses' => 'Mypleasure\Api\V1\Controller\UserController@update']);
  $api->delete('users/{id}',   ['as' => 'users.destroy', 'uses' => 'Mypleasure\Api\V1\Controller\UserController@destroy']);

  // Collection
  $api->get('collections',           ['as' => 'collections.index',   'uses' => 'Mypleasure\Api\V1\Controller\CollectionController@index']);
  $api->get('collections/{id}',      ['as' => 'collections.show',    'uses' => 'Mypleasure\Api\V1\Controller\CollectionController@show']);
  $api->post('collections',          ['as' => 'collections.store',   'uses' => 'Mypleasure\Api\V1\Controller\CollectionController@store']);
  $api->put('collections/{id}',      ['as' => 'collections.update',  'uses' => 'Mypleasure\Api\V1\Controller\CollectionController@update']);
  $api->delete('collections/{id}',   ['as' => 'collections.destroy', 'uses' => 'Mypleasure\Api\V1\Controller\CollectionController@destroy']);

  // Video
  $api->get('videos',           ['as' => 'videos.index',   'uses' => 'Mypleasure\Api\V1\Controller\VideoController@index']);
  $api->get('videos/{id}',      ['as' => 'videos.show',    'uses' => 'Mypleasure\Api\V1\Controller\VideoController@show']);
  $api->post('videos',          ['as' => 'videos.store',   'uses' => 'Mypleasure\Api\V1\Controller\VideoController@store']);
  $api->put('videos/{id}',      ['as' => 'videos.update',  'uses' => 'Mypleasure\Api\V1\Controller\VideoController@update']);
  $api->delete('videos/{id}',   ['as' => 'videos.destroy', 'uses' => 'Mypleasure\Api\V1\Controller\VideoController@destroy']);

  // Tags
  $api->get('tags',           ['as' => 'tags.index',   'uses' => 'Mypleasure\Api\V1\Controller\TagController@index']);
  $api->post('tags',          ['as' => 'tags.store',   'uses' => 'Mypleasure\Api\V1\Controller\TagController@store']);
  $api->get('tags/{id}',      ['as' => 'tags.show',    'uses' => 'Mypleasure\Api\V1\Controller\TagController@show']);
  $api->delete('tags/{id}',   ['as' => 'tags.destroy', 'uses' => 'Mypleasure\Api\V1\Controller\TagController@destroy']);

});
