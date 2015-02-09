<?php

/**
 * Home
 */
Route::get('/', array(
  'uses' => 'StaticPagesController@getHome',
  'as' => 'static.home'
));


/**
 * Register, login, logout
 */
Route::get('/register', array(
  'uses' => 'AuthController@getRegister',
  'as' => 'auth.register'
));

Route::post('/register', array(
  'uses' => 'UsersController@store',
  'before' => 'csrf'
));

Route::get('/login', array(
  'uses' => 'AuthController@getLogin',
  'as' => 'auth.login'
));

Route::post('/login', array(
  'uses' => 'AuthController@postLogin',
  'before' => 'csrf'
));

Route::get('/logout', array(
  'uses' => 'AuthController@getLogout',
  'as' => 'auth.logout',
  'before' => 'auth'
));


/**
 * Password reminder
 */
Route::controller('password', 'RemindersController');


/**
 * User account
 */
Route::get('/me', array(
  'uses' => 'ProfileController@getProfile',
  'as' => 'user.profile',
  'before' => 'auth'
));

Route::get('/me/edit/email', array(
  'uses' => 'ProfileController@getEditEmail',
  'as' => 'user.edit.email',
  'before' => 'auth'
));

Route::post('/me/edit/email', array(
  'uses' => 'UsersController@updateEmail',
  'before' => 'auth|csrf'
));

Route::get('/me/edit/password', array(
  'uses' => 'ProfileController@getEditPassword',
  'as' => 'user.edit.password',
  'before' => 'auth'
));

Route::post('/me/edit/password', array(
  'uses' => 'UsersController@updatePassword',
  'before' => 'auth|csrf'
));

Route::get('/me/videos', array(
  'uses' => 'VideosController@index',
  'as' => 'user.videos',
  'before' => 'auth'
));

Route::get('/me/videos/add', array(
  'uses' => 'ProfileController@getAddVideo',
  'as' => 'user.videos.add',
  'before' => 'auth'
));

Route::get('/me/videos/add/debug', array(
  'uses' => 'ProfileController@getAddVideoDebug',
  'as' => 'user.videos.add.debug',
  'before' => 'auth'
));

Route::post('/me/videos/add', array(
  'uses' => 'VideosController@store',
  'before' => 'auth|csrf'
));

Route::post('/me/videos/add/debug', array(
  'uses' => 'VideosController@storeDebug',
  'before' => 'auth|csrf'
));

Route::get('/me/videos/{videoId}/tags/edit', array(
  'uses' => 'TagsController@getEditTags',
  'as' => 'user.tags.edit',
  'before' => 'auth'
))->where(array('videoId' => '[0-9]+'));

Route::post('/me/videos/{videoId}/tags/edit', array(
  'uses' => 'TagsController@update',
  'before' => 'auth|csrf'
))->where(array('videoId' => '[0-9]+'));

Route::get('/me/delete', array(
  'uses' => 'ProfileController@getDelete',
  'as' => 'user.delete',
  'before' => 'auth'
));

Route::post('/me/delete', array(
  'uses' => 'UsersController@destroy',
  'before' => 'auth|csrf'
));