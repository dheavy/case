@extends('master')

@section('content')

  <div class="row">
    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      <h2>Sign in</h2>

      {{ HTML::ul($errors->all()) }}

      @if(Session::has('message'))
        <div class="alert alert-info">{{ Session::get('message') }}</div>
      @endif

      {{ Form::open(array('url' => URL::secure('/login'))) }}

      <div class="form-group">
        {{ Form::label('username', 'Username') }}
        {{ Form::text('username', Input::old('username'), array('class' => 'form-control')) }}
      </div>

      <div class="form-group">
        {{ Form::label('password', 'Password') }}
        {{ Form::password('password', array('class' => 'form-control')) }}
      </div>

        {{ Form::submit('Sign in', array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>

@stop
