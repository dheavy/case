@extends('master')

@section('content')
  <div class="row">
    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      <h2>{{ Lang::get('auth.register.title') }}</h2>
      @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
      @endif

      {{ HTML::ul($errors->all()) }}

      {{ Form::open(array('url' => URL::secure('/register'))) }}

      <div class="form-group">
        {{ Form::label('invite', Lang::get('auth.register.form.invite')) }}
        {{ Form::text('invite', Input::old('invite'), array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('username', Lang::get('auth.register.form.username')) }}
        {{ Form::text('username', Input::old('username'), array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('email', Lang::get('auth.register.form.email')) }}
        {{ Form::email('email', Input::old('email'), array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('password', Lang::get('auth.register.form.password')) }}
        {{ Form::password('password', array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('password_confirmation', Lang::get('auth.register.form.passwordconfirmation')) }}
        {{ Form::password('password_confirmation', array('class' => 'form-control')) }}
      </div>

        {{ Form::submit(Lang::get('auth.register.form.register'), array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>
@stop
