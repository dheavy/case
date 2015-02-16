@extends('master')

@section('content')
  <div class="row">
    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      <h2>Register</h2>
      @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
      @endif

      {{ HTML::ul($errors->all()) }}

      {{ Form::open(array('url' => URL::secure('/register'))) }}

      <div class="form-group">
        {{ Form::label('invite', 'Invite code') }}
        {{ Form::text('invite', Input::get('c', ''), array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('username', 'Username') }}
        {{ Form::text('username', Input::old('username'), array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('email', 'Email') }}
        {{ Form::email('email', Input::old('email'), array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('password', 'Password') }}
        {{ Form::password('password', array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('password_confirmation', 'Confirm password') }}
        {{ Form::password('password_confirmation', array('class' => 'form-control')) }}
      </div>

        {{ Form::submit('Sign up', array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>
@stop
