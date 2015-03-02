@extends('master')

@section('content')

  <div class="container">

    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">{{{ $user->username }}} {{ Lang::get('users.password.title') }}</h3>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ HTML::ul($errors->all()) }}

      @if (Session::has('message'))
        <div class="alert alert-info">{{{ Session::get('message') }}}</div>
      @endif
    </div>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ Form::open(array('url' => URL::secure('/me/edit/password'))) }}

        <div class="form-group">
          {{ Form::label('current_password', Lang::get('users.password.form.currentpassword')) }}
          {{ Form::password('current_password', array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
          {{ Form::label('password', Lang::get('users.password.form.password')) }}
          {{ Form::password('password', array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
          {{ Form::label('password_confirmation', Lang::get('users.password.form.passwordconfirmation')) }}
          {{ Form::password('password_confirmation', array('class' => 'form-control')) }}
        </div>

        {{ Form::submit(Lang::get('users.password.form.changepassword'), array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>

@stop