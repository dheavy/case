@extends('master')

@section('content')

  @if (Session::has('message'))
  <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @endif

  <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} (edit video)</h3>

  <div class="row">

    <div class="col-sm-12 col-md-6 col-lg-6 video">
      @if ($video->method === '_dummy')
      <div class="col-sm-12 col-md-12 col-lg-12 dummy" style="display:block;width:100%;height:200px;background:#CCC"></div>
      @else
      <img class="col-sm-12 col-md-12 col-lg-12" src="{{ $video->poster }}" width="100%">
      @endif
    </div>

    {{ Form::open(array('url' => URL::secure($url, [], true))) }}
    {{ Form::hidden('video', $video->id) }}
    <div class="col-sm-12 col-md-6 col-lg-6">
      <div class="form-group">
        {{ Form::label('title', 'Edit title', array('class' => 'control-label')) }}
        {{ Form::text('title', $video->title, array('class' => 'form-control col-sm-12 col-md-12 col-lg-12')) }}
      </div>
      <div class="form-group" style="padding-top:30px">
        <a href="{{{ URL::route('user.videos') }}}" class="btn btn-default" data-dismiss="modal">Cancel</a>
        <input type="submit" class="btn btn-primary" value="Update">
      </div>
    </div>
    {{ Form::close() }}

  </div>

@stop