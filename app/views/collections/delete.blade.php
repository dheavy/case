@extends('master')

@section('content')

  @if (Session::has('message'))
  <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @endif

  <div class="row">
    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">{{{ $user->username }}} (delete collection)</h3>
    <?php $url = URL::secure("/me/collections/{$collection->id}/delete"); ?>

    {{ Form::open(array('url' => $url)) }}
    {{ Form::hidden('collection', $collection->id) }}
    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      <h4>Are you sure you want to delete "{{{ $collection->name }}}"?</h4>

      <?php if ($hasVideos): ?>
      <div class="form-group">
        {{ Form::label('replace', 'What should we do with the videos in this collection?') }}
        {{ Form::select('replace', $replaceSelectList, null, array('class' => 'form-control')) }}
      </div>
      <?php endif; ?>

      <div class="form-group">
        <a href="{{{ URL::secure('/me/collections') }}}" class="btn btn-default" data-dismiss="modal">No, cancel</a>
        <input type="submit" class="btn btn-primary" value="Yes, delete">
      </div>
    </div>
  {{ Form::close() }}

  </div>

@stop