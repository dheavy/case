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

  // User
  $api->get('users',           ['as' => 'users.index',   'uses' => 'Mypleasure\Api\V1\Controller\UserController@index']);
  $api->get('users/{id}',      ['as' => 'users.show',    'uses' => 'Mypleasure\Api\V1\Controller\UserController@show']);
  $api->post('users',          ['as' => 'users.store',   'uses' => 'Mypleasure\Api\V1\Controller\UserController@store']);
  $api->put('users/{id}',      ['as' => 'users.update',  'uses' => 'Mypleasure\Api\V1\Controller\UserController@update']);
  $api->delete('users/{id}',   ['as' => 'users.destroy', 'uses' => 'Mypleasure\Api\V1\Controller\UserController@destroy']);

  $api->get('users/{uid}/collections',          ['as' => 'users.collections.index', 'uses' => 'Mypleasure\Api\V1\Controller\UserCollectionController@index']);
  $api->get('users/{uid}/collections/{cid}',    ['as' => 'users.collections.show', 'uses' => 'Mypleasure\Api\V1\Controller\UserCollectionController@show']);
  $api->put('users/{uid}/collections/{cid}',    ['as' => 'users.collections.update', 'uses' => 'Mypleasure\Api\V1\Controller\UserCollectionController@update']);
  $api->delete('users/{uid}/collections/{cid}', ['as' => 'users.collections.destroy', 'uses' => 'Mypleasure\Api\V1\Controller\UserCollectionController@destroy']);

  $api->get('users/{uid}/videos',          ['as' => 'users.videos.index', 'uses' => 'Mypleasure\Api\V1\Controller\UserCollectionController@index']);
  $api->get('users/{uid}/videos/{cid}',    ['as' => 'users.videos.show', 'uses' => 'Mypleasure\Api\V1\Controller\UserCollectionController@show']);
  $api->put('users/{uid}/videos/{cid}',    ['as' => 'users.videos.update', 'uses' => 'Mypleasure\Api\V1\Controller\UserCollectionController@update']);
  $api->delete('users/{uid}/videos/{cid}', ['as' => 'users.videos.destroy', 'uses' => 'Mypleasure\Api\V1\Controller\UserCollectionController@destroy']);

  // Collection
  $api->get('collections',           ['as' => 'collections.index',   'uses' => 'Mypleasure\Api\V1\Controller\CollectionController@index']);
  $api->get('collections/{id}',      ['as' => 'collections.show',    'uses' => 'Mypleasure\Api\V1\Controller\CollectionController@show']);
  $api->put('collections/{id}',      ['as' => 'collections.update',  'uses' => 'Mypleasure\Api\V1\Controller\CollectionController@update']);
  $api->delete('collections/{id}',   ['as' => 'collections.destroy', 'uses' => 'Mypleasure\Api\V1\Controller\CollectionController@destroy']);

  // Video
  $api->get('videos',           ['as' => 'videos.index',   'uses' => 'Mypleasure\Api\V1\Controller\VideoController@index']);
  $api->get('videos/{id}',      ['as' => 'videos.show',    'uses' => 'Mypleasure\Api\V1\Controller\VideoController@show']);
  $api->put('videos/{id}',      ['as' => 'videos.update',  'uses' => 'Mypleasure\Api\V1\Controller\VideoController@update']);
  $api->delete('videos/{id}',   ['as' => 'videos.destroy', 'uses' => 'Mypleasure\Api\V1\Controller\VideoController@destroy']);

  // Video
  $api->get('tags',           ['as' => 'tags.index',   'uses' => 'Mypleasure\Api\V1\Controller\TagController@index']);
  $api->get('tags/{id}',      ['as' => 'tags.show',    'uses' => 'Mypleasure\Api\V1\Controller\TagController@show']);
  $api->delete('tags/{id}',   ['as' => 'tags.destroy', 'uses' => 'Mypleasure\Api\V1\Controller\TagController@destroy']);

});
