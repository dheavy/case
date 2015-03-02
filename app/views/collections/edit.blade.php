@extends('master')

@section('content')

  <div class="container">

    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">{{{ $user->username }}} {{ Lang::get('collections.edit.title', array('name' => $collection->name)) }}</h3>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ HTML::ul($errors->all()) }}

      @if (Session::has('message'))
        <div class="alert alert-info">{{{ Session::get('message') }}}</div>
      @endif
    </div>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      <?php $url = URL::secure("/me/collections/" . $collection->id . "/edit") ?>
      {{ Form::open(array('url' => $url)) }}
      {{ Form::hidden('collection', $collection->id) }}
        <div class="form-group">
          {{ Form::label('name', Lang::get('collections.edit.form.name')) }}
          {{ Form::text('name', $collection->name, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
          <?php $status = $collection->status; ?>
          {{ Form::label('status', Lang::get('collections.edit.form.visibility')) }}
          {{ Form::select('status', array('1' => Lang::get('collections.edit.form.public'), '0' => Lang::get('collections.edit.form.private')), $status, array('class' => 'form-control')) }}
        </div>

        <a href="{{{ URL::secure('/me/collections') }}}" class="btn btn-default">{{ Lang::get('collections.edit.form.cancel') }}</a>
        {{ Form::submit(Lang::get('collections.edit.form.update'), array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>

@stop