@extends('master')

@section('content')

  <div class="row">
    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      <h2>{{ Lang::get('auth.login.title') }}</h2>

      {{ HTML::ul($errors->all()) }}

      @if(Session::has('message'))
        <div class="alert alert-info">{{ Session::get('message') }}</div>
      @endif

      {{ Form::open(array('url' => URL::secure('/login'))) }}

      <div class="form-group">
        {{ Form::label('username', Lang::get('auth.login.form.username')) }}
        {{ Form::text('username', Input::old('username'), array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('password', Lang::get('auth.login.form.password')) }}
        {{ Form::password('password', array('class' => 'form-control')) }}
      </div>

        {{ Form::submit(Lang::get('auth.login.form.login'), array('class' => 'btn btn-primary')) }}
        <a href="{{URL::action('RemindersController@getRemind')}}" style="margin-left:1em">{{ Lang::get('auth.login.form.forgot') }}</a>
      {{ Form::close() }}
    </div>
  </div>

@stop
