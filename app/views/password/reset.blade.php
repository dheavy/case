@extends('master')

@section('content')

  <div class="row">
    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      @if (Session::has('status'))
        <div class="alert alert-info">{{{ Session::get('status') }}}</div>
      @elseif (Session::has('error'))
        <div class="alert alert-info">{{{ Session::get('error') }}}</div>
      @endif

      <h3 class="col-sm-12 col-md-12 col-lg-12">Reset your forgotten password</h3>

      <div class="col-sm-12 col-md-12 col-lg-12">
        {{ HTML::ul($errors->all()) }}

        @if (Session::has('message'))
          <div class="alert alert-info">{{{ Session::get('message') }}}</div>
        @endif
      </div>

      <div class="col-sm-12 col-md-12 col-lg-12">
        <h5>Use the following form to reset your forgotten password.</h5>
      </div>

       <div class="col-sm-12 col-md-12 col-lg-12">
        {{ Form::open(array('action' => 'RemindersController@postReset')) }}

          {{ Form::hidden('token', $token) }}

          <div class="form-group">
            {{ Form::label('email', 'Email') }}
            {{ Form::email('email', '', array('class' => 'form-control')) }}
          </div>

          <div class="form-group">
            {{ Form::label('password', 'Password') }}
            {{ Form::password('password', array('class' => 'form-control')) }}
          </div>

          <div class="form-group">
            {{ Form::label('password_confirmation', 'Confirm password') }}
            {{ Form::password('password_confirmation', array('class' => 'form-control')) }}
          </div>

        {{ Form::submit('Reset password', array('class' => 'btn btn-primary')) }}
      </div>
    </div>
  </div>

@stop