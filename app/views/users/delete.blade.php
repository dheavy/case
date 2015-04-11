@extends('master')

@section('content')

  <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">{{{ $user->username }}} {{ Lang::get('users.delete.title') }}</h3>

  <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
    {{ Lang::get('users.delete.message') }}
  </div>

  <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
    {{ HTML::ul($errors->all()) }}

    <?php if (Session::has('message')): ?>
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    <?php elseif (Session::has('message_fallback')): ?>
      <div class="alert alert-info">{{{ Session::get('message_fallback') }}}</div>
      <?php Session::forget('message_fallback'); ?>
    <?php endif; ?>
  </div>

  <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
    {{ Form::open(array('url' => URL::secure('/me/delete'))) }}

      <div class="form-group">
        {{ Form::label('password', Lang::get('users.delete.form.password')) }}
        {{ Form::password('password', array('class' => 'form-control')) }}
      </div>

        {{ Form::submit(Lang::get('users.delete.form.delete'), array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
  </div>

@stop