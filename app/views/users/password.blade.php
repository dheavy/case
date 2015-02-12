@extends('master')

@section('content')

  <div class="container">
    @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} (edit password)</h3>

    <div class="col-sm-12 col-md-12 col-lg-12">
      {{ HTML::ul($errors->all()) }}
    </div>

    <div class="col-sm-12 col-md-12 col-lg-12">
      {{ Form::open(array('url' => URL::secure('/me/edit/password'))) }}

        <div class="form-group">
          {{ Form::label('current_password', 'Current Password') }}
          {{ Form::password('current_password', array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
          {{ Form::label('password', 'New Password') }}
          {{ Form::password('password', array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
          {{ Form::label('password_confirmation', 'Confirm New Password') }}
          {{ Form::password('password_confirmation', array('class' => 'form-control')) }}
        </div>

        {{ Form::submit('Change password', array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>

@stop