<?php

/**
 * Home
 */
Route::get('/', array(
  'https' => true,
  'uses' => 'StaticPagesController@getHome',
  'as' => 'static.home'
));


/**
 * Register, login, logout
 */
Route::get('/register', array(
  'https' => true,
  'uses' => 'AuthController@getRegister',
  'as' => 'auth.register'
));

Route::post('/register', array(
  'https' => true,
  'uses' => 'UsersController@store',
  'before' => 'csrf'
));

Route::get('/login', array(
  'https' => true,
  'uses' => 'AuthController@getLogin',
  'as' => 'auth.login'
));

Route::post('/login', array(
  'https' => true,
  'uses' => 'AuthController@postLogin',
  'before' => 'csrf'
));

Route::get('/logout', array(
  'https' => true,
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
  'https' => true,
  'uses' => 'ProfileController@getProfile',
  'as' => 'user.profile',
  'before' => 'auth'
));

Route::get('/me/edit/email', array(
  'https' => true,
  'uses' => 'ProfileController@getEditEmail',
  'as' => 'user.edit.email',
  'before' => 'auth'
));

Route::post('/me/edit/email', array(
  'https' => true,
  'uses' => 'UsersController@updateEmail',
  'before' => 'auth|csrf'
));

Route::get('/me/edit/password', array(
  'https' => true,
  'uses' => 'ProfileController@getEditPassword',
  'as' => 'user.edit.password',
  'before' => 'auth'
));

Route::post('/me/edit/password', array(
  'https' => true,
  'uses' => 'UsersController@updatePassword',
  'before' => 'auth|csrf'
));

Route::get('/me/videos', array(
  'https' => true,
  'uses' => 'VideosController@index',
  'as' => 'user.videos',
  'before' => 'auth'
));

Route::get('/me/videos/add', array(
  'https' => true,
  'uses' => 'ProfileController@getAddVideo',
  'as' => 'user.videos.add',
  'before' => 'auth'
));

Route::get('/me/videos/add/debug', array(
  'https' => true,
  'uses' => 'ProfileController@getAddVideoDebug',
  'as' => 'user.videos.add.debug',
  'before' => 'auth'
));

Route::post('/me/videos/add', array(
  'https' => true,
  'uses' => 'VideosController@store',
  'before' => 'auth|csrf'
));

Route::post('/me/videos/add/debug', array(
  'https' => true,
  'uses' => 'VideosController@storeDebug',
  'before' => 'auth|csrf'
));

Route::get('/me/videos/{videoId}/tags/edit', array(
  'https' => true,
  'uses' => 'TagsController@getEditTags',
  'as' => 'user.tags.edit',
  'before' => 'auth'
))->where(array('videoId' => '[0-9]+'));

Route::post('/me/videos/{videoId}/tags/edit', array(
  'https' => true,
  'uses' => 'TagsController@update',
  'before' => 'auth|csrf'
))->where(array('videoId' => '[0-9]+'));

Route::get('/me/delete', array(
  'https' => true,
  'uses' => 'ProfileController@getDelete',
  'as' => 'user.delete',
  'before' => 'auth'
));

Route::post('/me/delete', array(
  'https' => true,
  'uses' => 'UsersController@destroy',
  'before' => 'auth|csrf'
));