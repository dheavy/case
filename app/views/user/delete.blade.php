@extends('master')

@section('content')

  @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} (delete account)</h3>

    <div class="col-sm-12 col-md-12 col-lg-12">
      <p>We're sorry to see you go... Enter your password below to delete your account.</p>
    </div>

    <div class="col-sm-12 col-md-12 col-lg-12">
      {{ HTML::ul($errors->all()) }}
    </div>

    <div class="col-sm-12 col-md-12 col-lg-12">
      {{ Form::open(array('url' => URL::secure('/me/delete', [], true))) }}

        <div class="form-group">
          {{ Form::label('password', 'Password') }}
          {{ Form::password('password', array('class' => 'form-control')) }}
        </div>

          {{ Form::submit('Delete account', array('class' => 'btn btn-primary')) }}
        {{ Form::close() }}
    </div>

@stop