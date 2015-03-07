@extends('master')

@section('content')

  @if (Session::has('message'))
  <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @endif

  <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} {{ Lang::get('videos.edit.title') }}</h3>

  <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
    {{ HTML::ul($errors->all()) }}
  </div>

  <div class="row">

    <div class="col-sm-12 col-md-6 col-lg-6 video">
      <img class="col-sm-12 col-md-12 col-lg-12 img-rounded" src="{{ $video->poster }}" width="100%">
    </div>

    <?php
      $actionUrl = URL::secure("/me/videos/{$video->id}/edit");
      $backUrl = URL::secure("/me/videos");
    ?>
    {{ Form::open(array('url' => $actionUrl)) }}
    {{ Form::hidden('video', $video->id) }}
    <div class="col-sm-12 col-md-6 col-lg-6">
      <div class="form-group">
        {{ Form::label('title', Lang::get("videos.edit.form.edittitle"), array('class' => 'control-label')) }}
        {{ Form::text('title', $video->title, array('class' => 'form-control col-sm-12 col-md-12 col-lg-12')) }}
      </div>
      <div class="form-group" style="padding-top:30px">
        <a href="{{{ $backUrl }}}" class="btn btn-default" data-dismiss="modal">{{ Lang::get('videos.edit.form.cancel') }}</a>
        <input type="submit" class="btn btn-primary" value="{{ Lang::get('videos.edit.form.update') }}">
      </div>
    </div>
    {{ Form::close() }}

  </div>

@stop