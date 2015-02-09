@extends('master')

@section('content')

  <div class="container">
    @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} (edit email)</h3>

    <div class="col-sm-12 col-md-12 col-lg-12">
      {{ HTML::ul($errors->all()) }}
    </div>

    <div class="col-sm-12 col-md-12 col-lg-12">
      <h5>Email address is not mandatory, but without it you can not retrieve your forgotten password.</h5>
      <p>Your privacy is our top priority. We are paranoid about it. <a href="#">Read our privacy policy</a>.</p>
    </div>

    <div class="col-sm-12 col-md-12 col-lg-12">
      {{ Form::open(array('url' => URL::to('/me/edit/email', [], true))) }}

        <div class="form-group">
          {{ Form::label('email', 'Email') }}

          @if ($user->hasPlaceholderEmail())
          {{ Form::email('email', '', array('class' => 'form-control')) }}
          @else
          {{ Form::email('email', $user->email, array('class' => 'form-control')) }}
          @endif
        </div>

        {{ Form::submit('Update email address', array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>

@stop