@extends('master')

@section('content')

  <div class="row">
    @if (Session::has('message'))
    <div class="alert alert-info col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">{{{ Session::get('message') }}}</div>
    @endif

    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">ADMIN — {{{ $user->username }}}</h3>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ HTML::ul($errors->all()) }}
    </div>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ Form::open(array('url' => URL::secure('/admin/invites/create'))) }}

        <div class="form-group">
          {{ Form::label('email', "Recipient's email") }}
          {{ Form::email('email', '', array('class' => 'form-control')) }}
        </div>

        {{ Form::submit('Send invite', array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>

@stop
