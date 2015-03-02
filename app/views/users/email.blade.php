@extends('master')

@section('content')

  <div class="container">

    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">{{{ $user->username }}} {{ Lang::get('users.email.title') }}</h3>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ HTML::ul($errors->all()) }}

      @if (Session::has('message'))
        <div class="alert alert-info">{{{ Session::get('message') }}}</div>
      @endif
    </div>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ Lang::get('users.email.message') }}
    </div>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ Form::open(array('url' => URL::secure('/me/edit/email'))) }}

        <div class="form-group">
          {{ Form::label('email', Lang::get('users.email.form.email')) }}

          @if ($user->hasPlaceholderEmail())
          {{ Form::email('email', '', array('class' => 'form-control')) }}
          @else
          {{ Form::email('email', $user->email, array('class' => 'form-control')) }}
          @endif
        </div>

        {{ Form::submit(Lang::get('users.email.form.update'), array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>

@stop