@extends('master')

@section('content')

  @if (Session::has('message'))
  <section class="alert alert-info">{{{ Session::get('message') }}}</section>
  @endif

  <h3 class="col-sm-12 col-md-12 col-lg-12">Hello stranger... Please register or sign in.</h3>

  <nav class="col-sm-12 col-md-12 col-lg-12">
    <ul>
      <li><a href="{{{ URL::route('auth.register') }}}">register</a></li>
      <li><a href="{{{ URL::route('auth.login') }}}">sign in</a></li>
    </ul>
  </nav>

@stop
