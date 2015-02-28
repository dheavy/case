@extends('master')

@section('content')

  @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">{{{ $user->username }}} {{ Lang::get('users.delete.title') }}</h3>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ Lang::get('users.delete.message') }}
    </div>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ HTML::ul($errors->all()) }}
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