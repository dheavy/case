@extends('master')

@section('content')

  <div class="container">
    @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} (add video)</h3>

    <div class="col-sm-12 col-md-12 col-lg-12">
      {{ HTML::ul($errors->all()) }}
    </div>

    <div class="col-sm-12 col-md-12 col-lg-12">
      <p>To curate a video, enter the URL of its page in the field below.</p>
    </div>

    <div class="col-sm-12 col-md-12 col-lg-12">
      {{ Form::open(array('url' => URL::secure('/me/videos/add'))) }}

        <div class="form-group">
          {{ Form::label('url', 'Page URL') }}
          {{ Form::text('url', '', array('class' => 'form-control')) }}
        </div>

        {{ Form::submit('Add video', array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>

@stop