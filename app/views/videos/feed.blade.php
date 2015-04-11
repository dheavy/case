@extends('master')

@section('content')

  <?php if (Session::has('message')): ?>
    <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  <?php elseif (Session::has('message_fallback')): ?>
    <div class="alert alert-info">{{{ Session::get('message_fallback') }}}</div>
    <?php Session::forget('message_fallback'); ?>
  <?php endif; ?>

  <div class="row">
    <h3 class="col-sm-12 col-md-12 col-lg-12">{{ Lang::get('videos.feed.title') }}</h3>
    <span class="col-sm-12 col-md-12 col-lg-12">{{ Lang::get('videos.feed.subtitle') }}</span>
  </div>

  <div class="row videos">
    @foreach($videos as $i => $v)
      @include('videos.partials.single', array('id' => $v->video->id, 'poster' => $v->video->poster, 'embed_url' => $v->video->embed_url, 'method' => $v->video->method, 'title' => $v->video->title, 'duration' => $v->video->duration, 'isNsfw' => $v->video->isNsfw(), 'index' => $i))
    @endforeach
  </div>

  @include('videos.partials.player')

@stop