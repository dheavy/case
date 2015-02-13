@extends('master')

@section('content')

  @if (Session::has('message'))
  <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @endif

  <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} (delete video)</h3>

  <div class="row">

    <div class="col-sm-12 col-md-6 col-lg-6 video">
      @if ($video->method === '_dummy')
      <div class="col-sm-12 col-md-12 col-lg-12 dummy" style="display:block;width:100%;height:200px;background:#CCC"></div>
      @else
      <img class="col-sm-12 col-md-12 col-lg-12" src="{{ $video->poster }}" width="100%">
      @endif

      <h5 class="col-sm-12 col-md-12 col-lg-12">{{{ $video->title }}}</h5>
      <div class="col-sm-12 col-md-12 col-lg-12">{{{ $video->duration }}}</div>
    </div>

    <?php $url = URL::secure("/me/videos/{$video->id}/delete") ?>
    {{ Form::open(array('url' => $url)) }}
      {{ Form::hidden('video', $video->id) }}
      <div class="col-sm-12 col-md-6 col-lg-6">
        <h4>Are you sure you want to delete this video?</h4>
        <a href="{{{ URL::secure('/me/videos') }}}" class="btn btn-default" data-dismiss="modal">No, cancel</a>
        <input type="submit" class="btn btn-primary" value="Yes, delete">
      </div>
    {{ Form::close() }}

  </div>

@stop