@extends('master')

@section('content')

  {{ HTML::style('css/bootstrap-tagsinput.css', [], true) }}

  <div class="container">
    <?php if (Session::has('message')): ?>
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    <?php elseif (Session::has('message_fallback')): ?>
      <div class="alert alert-info">{{{ Session::get('message_fallback') }}}</div>
      <?php Session::forget('message_fallback'); ?>
    <?php endif; ?>

    <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} {{ Lang::get('tags.title') }}</h3>

    <div class="col-sm-12 col-md-4 col-lg-4 video">
      <h5 class="col-sm-12 col-md-12 col-lg-12">{{{ $video->title }}}</h5>
      <img class="col-sm-12 col-md-12 col-lg-12" src="{{ $video->poster }}" width="100%">
    </div>

    <div class="col-sm-12 col-md-8 col-lg-8">
      <?php
        $actionUrl = URL::secure("/me/videos/{$video->id}/tags/edit");
        $backUrl = URL::secure('/me/videos');
      ?>
      {{ Form::open(array('url' => $actionUrl)) }}
        <h5>Tags</h5>
        {{ Form::hidden('video', $video->id) }}
        <div class="form-group">
          {{ Form::text('tags', $tags, array('data-role' => 'tagsinput')) }}
        </div>

        <a href="{{{ $backUrl }}}" class="btn btn-default" data-dismiss="modal">{{ Lang::get('tags.form.cancel') }}</a>
        {{ Form::submit(Lang::get('tags.form.update'), array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>

  {{ HTML::script('js/bootstrap-tagsinput.min.js', [], true) }}

@stop