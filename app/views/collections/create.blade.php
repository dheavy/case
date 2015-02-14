@extends('master')

@section('content')

  <div class="container">
    @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">{{{ $user->username }}} (create collection)</h3>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ HTML::ul($errors->all()) }}
    </div>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ Form::open(array('url' => URL::secure('/me/collections/create'))) }}

        <div class="form-group">
          {{ Form::label('name', 'Name your new collection') }}
          {{ Form::text('name', '', array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
          {{ Form::label('status', 'Visibility to other site members') }}
          {{ Form::select('status', array('1' => 'public', '0' => 'private'), '', array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
          {{ Form::submit('Create collection', array('class' => 'btn btn-primary')) }}
        </div>
      {{ Form::close() }}
    </div>
  </div>

@stop