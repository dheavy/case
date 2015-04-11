@extends('master')

@section('content')

  <style>
    ul, li {
      list-style-type: none;
      margin: 0;
      padding: 0;
    }

    .info {
      margin-top: 2em;
    }
  </style>

  <?php dd(Session::all()); ?>

  <div class="row">
    @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    @if ($user->role->name === 'admin')
      <h3 class="col-sm-12 col-md-12 col-lg-12">ADMIN — {{{ $user->username }}}</h3>
    @elseif ($user->role->name === 'curator')
      <h3 class="col-sm-12 col-md-12 col-lg-12">Hi, {{{ $user->username }}}</h3>
    @endif

    <nav class="col-sm-12 col-md-4 col-lg-4">
      <h4>Raccourcis</h4>
      <ul>
        <li><a href="{{{ URL::secure('/me/edit/email') }}}">{{ Lang::get('master.nav.changeemail') }}</a></li>
        <li><a href="{{{ URL::secure('/me/edit/password') }}}">{{ Lang::get('master.nav.changepassword') }}</a></li>
        <li><a href="{{{ URL::secure('/me/videos/create') }}}">{{ Lang::get('master.nav.addvideo') }}</a></li>
        <li><a href="{{{ URL::secure('/me/delete') }}}">{{ Lang::get('users.profile.delete') }}</a></li>

        @if ($user->role->name === 'admin')
        <li><a href="{{{ URL::secure('/admin/invites/create') }}}">{{Lang::get('users.profile.invite') }}</a></li>
        <li><a href="{{{ URL::secure('') }}}">{{ Lang::get('users.profile.listusers') }}</a></li>
        @endif
      </ul>
    </nav>

    <section class="col-sm-12 col-md-5 col-lg-5">
      <h4>Bookmarklet</h4>
      <div class="info">
        <p>Faites glisser le bouton ci-dessous dans la barre de favoris de votre navigateur.<br>
        Utilisez-le pour collectionner vos vidéos.</p>
        <p>
          <a href="javascript:(function(){var s=document.createElement('script');var r=window.location.href.indexOf('localhost')!=-1?'http://localhost:9999':'https://still-mountain-6425.herokuapp.com';s.src=r+'/kipp/bookmarklet.js';document.body.appendChild(s);})()" class="btn btn-default" style="padding-top:10px;padding-left:30px;padding-right:30px">   ❤   </a>
        </p>
      </div>

      <h4>{{ Lang::get('users.profile.infos') }}</h4>
      <div class="info">
        <h5>01/03/2015 - Mise à jour</h5>
        <p>Mise à jour du site avant la première session de test alpha.</p>
      </div>
    </section>

    <section class="col-sm-12 col-md-3 col-lg-3">
      <?php
      $numCollection = $user->collections->count();
      $numVideos = count($user->videos());
      ?>

      <h4>{{ Lang::get('users.profile.stats') }}</h4>
      <p>{{ Lang::choice('users.profile.numcollection', $numCollection, array('count' => $numCollection)) }} {{ Lang::choice('users.profile.numvideos', $numVideos, array('count' => $numVideos)) }}</p>
    </section>
  </div>

@stop
