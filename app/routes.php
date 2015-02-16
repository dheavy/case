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
 * Feed
 */

Route::get('/feed', array(
  'uses' => 'VideosController@feed',
  'as' => 'feed',
  'before' => 'auth'
));

/**
 * User account
 */

// Show profile page.
Route::get('/me', array(
  'uses' => 'UsersController@getProfile',
  'as' => 'users.profile',
  'before' => 'auth'
));

// Edit email.
Route::get('/me/edit/email', array(
  'uses' => 'UsersController@getEditEmail',
  'as' => 'users.email',
  'before' => 'auth'
));

Route::post('/me/edit/email', array(
  'uses' => 'UsersController@updateEmail',
  'before' => 'auth|csrf'
));

// Edit password.
Route::get('/me/edit/password', array(
  'uses' => 'UsersController@getEditPassword',
  'as' => 'users.password',
  'before' => 'auth'
));

Route::post('/me/edit/password', array(
  'uses' => 'UsersController@updatePassword',
  'before' => 'auth|csrf'
));

// Show video page.
Route::get('/me/videos', array(
  'uses' => 'VideosController@index',
  'as' => 'videos.index',
  'before' => 'auth'
));

// Add video.
Route::get('/me/videos/create', array(
  'uses' => 'VideosController@getAddVideo',
  'as' => 'videos.create',
  'before' => 'auth'
));

Route::get('/me/videos/create/debug', array(
  'uses' => 'VideosController@getAddVideoDebug',
  'as' => 'videos.create.debug',
  'before' => 'auth'
));

Route::post('/me/videos/create', array(
  'uses' => 'VideosController@store',
  'before' => 'auth|csrf'
));

Route::post('/me/videos/create/debug', array(
  'uses' => 'VideosController@storeDebug',
  'before' => 'auth|csrf'
));

// Collections
Route::get('/me/collections/', array(
  'uses' => 'CollectionsController@index',
  'as' => 'collections.index',
  'before' => 'auth'
));

Route::get('/me/collections/{id}', array(
  'uses' => 'CollectionsController@getCollection',
  'before' => 'auth'
))->where(array('id' => '[0-9]+'));

// Create collection
Route::get('/me/collections/create', array(
  'uses' => 'CollectionsController@getCreateCollection',
  'as' => 'collections.create',
  'before' => 'auth'
));

Route::post('/me/collections/create', array(
  'uses' => 'CollectionsController@store',
  'before' => 'auth|csrf'
));

// Edit collection
Route::get('/me/collections/{collectionId}/edit', array(
  'uses' => 'CollectionsController@getEditCollection',
  'as' => 'collections.edit',
  'before' => 'auth'
))->where(array('collectionId' => '[0-9]+'));

Route::post('/me/collections/{collectionId}/edit', array(
  'uses' => 'CollectionsController@update',
  'before' => 'auth|csrf'
))->where(array('collectionId' => '[0-9]+'));

// Delete collection
Route::get('/me/collections/{collectionId}/delete', array(
  'uses' => 'CollectionsController@getDeleteCollection',
  'as' => 'collections.delete',
  'before' => 'auth'
))->where(array('collectionId' => '[0-9]+'));

Route::post('/me/collections/{collectionId}/delete', array(
  'uses' => 'CollectionsController@destroy',
  'before' => 'auth|csrf'
))->where(array('collectionId' => '[0-9]+'));

// Edit videos.
Route::get('/me/videos/{videoId}/edit', array(
  'uses' => 'VideosController@getEditVideo',
  'as' => 'videos.edit',
  'before' => 'auth'
))->where(array('videoId' => '[0-9]+'));

Route::post('/me/videos/{videoId}/edit', array(
  'uses' => 'VideosController@update',
  'before' => 'auth|csrf'
))->where(array('videoId' => '[0-9]+'));

// Edit video tags.
Route::get('/me/videos/{videoId}/tags/edit', array(
  'uses' => 'TagsController@getEditTags',
  'as' => 'tags.edit',
  'before' => 'auth'
))->where(array('videoId' => '[0-9]+'));

Route::post('/me/videos/{videoId}/tags/edit', array(
  'uses' => 'TagsController@update',
  'before' => 'auth|csrf'
))->where(array('videoId' => '[0-9]+'));

// Delete video.
Route::get('/me/videos/{videoId}/delete', array(
  'uses' => 'VideosController@getDeleteVideo',
  'as' => 'videos.delete',
  'before' => 'auth'
))->where(array('videoId' => '[0-9]+'));

Route::post('/me/videos/{videoId}/delete', array(
  'uses' => 'VideosController@destroy',
  'before' => 'auth|csrf'
))->where(array('videoId' => '[0-9]+'));

// Delete account.
Route::get('/me/delete', array(
  'uses' => 'UsersController@getDelete',
  'as' => 'users.delete',
  'before' => 'auth'
));

Route::post('/me/delete', array(
  'uses' => 'UsersController@destroy',
  'before' => 'auth|csrf'
));

/**
 * Admin
 */

// Invites
Route::get('/admin/invites/create', array(
  'uses' => 'InvitesController@getCreateInvite',
  'as' => 'invites.create',
  'before' => 'auth|role:admin'
));

Route::post('/admin/invites/create', array(
  'uses' => 'InvitesController@store',
  'before' => 'auth|csrf|role:admin'
));