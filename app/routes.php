<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', array(
  'uses' => 'StaticPagesController@getHome',
  'as' => 'static.home'
));



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



Route::controller('password', 'RemindersController');



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

Route::post('/me/videos/add', array(
  'uses' => 'VideosController@store',
  'before' => 'auth|csrf'
));