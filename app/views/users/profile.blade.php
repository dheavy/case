@extends('master')

@section('content')

  <div class="row">
    @if (Session::has('message'))
    <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    @if ($user->role->name === 'admin')
    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">ADMIN — {{{ $user->username }}}</h3>
    @elseif ($user->role->name === 'curator')
    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">Hi, {{{ $user->username }}}</h3>
    @endif

    <nav class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      <ul>
        <li><a href="{{{ URL::secure('/me/edit/password') }}}">Change my password</a></li>
        <li><a href="{{{ URL::secure('/me/edit/email') }}}">Edit my email</a></li>
        <li><a href="{{{ URL::secure('/me/videos/create') }}}">Add video</a></li>
        <li><a href="{{{ URL::secure('/me/delete') }}}">Delete my account</a></li>

        @if ($user->role->name === 'admin')
        <li><hr><a href="{{{ URL::secure('/admin/invites/create') }}}">Generate and send invite</a></li>
        <li><a href="{{{ URL::secure('') }}}">List users</a></li>
        @endif

        @if (App::environment() === 'local')
        <li><hr><a href="{{{ URL::secure('/me/videos/create/debug') }}}">[DEBUG] Add fake video</a></li>
        @endif
      </ul>
    </nav>

    <section class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      You have {{{ $user->collections->count() }}}

      @if ($user->collections->count() > 1)
      collections,
      @else
      collection,
      @endif

      and curated {{{ count($user->videos()) }}}

      @if (count($user->videos()) > 1)
      videos.
      @else
      video.
      @endif
    </section>
  </div>

@stop
