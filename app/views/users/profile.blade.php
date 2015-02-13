@extends('master')

@section('content')

  @if (Session::has('message'))
  <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @endif

  <h3 class="col-sm-12 col-md-12 col-lg-12">Hi, {{{ $user->username }}}</h3>

  <nav class="col-sm-12 col-md-12 col-lg-12">
    <ul>
      <li><a href="{{{ URL::secure('/me/edit/password') }}}">Change my password</a></li>
      <li><a href="{{{ URL::secure('/me/edit/email') }}}">Edit my email</a></li>

      @if ($user->role->name === 'admin')
      <li><a href="{{{ URL::secure('admin.users.show') }}}">List users</a></li>
      @elseif ($user->role->name === 'curator')
      <li><a href="{{{ URL::secure('/me/videos/create') }}}">Add video</a></li>
      @endif

      <li><a href="{{{ URL::secure('/me/delete') }}}">Delete my account</a></li>

      @if (App::environment() === 'local')
      <li><hr><a href="{{{ URL::secure('/me/videos/create/debug') }}}">[DEBUG] Add fake video</a></li>
      @endif
    </ul>
  </nav>

  <section class="col-sm-12 col-md-12 col-lg-12">
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

@stop
