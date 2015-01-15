@extends('master')

@section('content')

  @if (Session::has('message'))
  <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @endif

  <h3 class="col-sm-12 col-md-12 col-lg-12">Hi, {{{ $user->username }}}</h3>

  <nav class="col-sm-12 col-md-12 col-lg-12">
    <ul>
      <li><a href="{{{ URL::route('user.edit.password') }}}">Change my password</a></li>
      <li><a href="{{{ URL::route('user.edit.email') }}}">Add or remove my email</a></li>

      @if ($user->role->name === 'admin')
      <li><a href="{{{ URL::route('admin.show.users') }}}">List users</a></li>
      @elseif ($user->role->name === 'curator')
      <li><a href="#">Add video</a></li>
      @endif
    </ul>
  </nav>

  <section class="col-sm-12 col-md-12 col-lg-12">
    Hello world
  </section>

@stop
