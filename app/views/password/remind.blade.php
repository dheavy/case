@extends('master')

@section('content')

  <div class="container">
    @if (Session::has('status'))
      <div class="alert alert-info">{{{ Session::get('status') }}}</div>
    @elseif (Session::has('error'))
      <div class="alert alert-info">{{{ Session::get('error') }}}</div>
    @endif

    <h3 class="col-sm-12 col-md-12 col-lg-12">Password reminder</h3>

    <div class="col-sm-12 col-md-12 col-lg-12">
      {{ HTML::ul($errors->all()) }}
    </div>

    <div class="col-sm-12 col-md-12 col-lg-12">
      <h5>If saved your email in your account, you may use the following form to reset your forgotten password.</h5>
    </div>

    <div class="col-sm-12 col-md-12 col-lg-12">
      {{ Form::open(array('action' => 'RemindersController@postRemind')) }}

        <div class="form-group">
          {{ Form::label('email', 'Email') }}
          {{ Form::email('email', '', array('class' => 'form-control')) }}
        </div>

      {{ Form::submit('Send reminder', array('class' => 'btn btn-primary')) }}
    </div>
  </div>

@stop