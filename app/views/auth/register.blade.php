@extends('master')

@section('content')
  <h2>Register</h2>

  {{ HTML::ul($errors->all()) }}

  {{ Form::open(array('url' => URL::secure('/register'))) }}

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
@stop
