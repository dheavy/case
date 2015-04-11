@extends('master')

@section('content')

  <?php if (Session::has('message')): ?>
    <div class="alert alert-info col-sm-12 col-md-10 col-lg-10 col-md-offset-2 col-lg-offset-2">{{{ Session::get('message') }}}</div>
  <?php elseif (Session::has('message_fallback')): ?>
    <div class="alert alert-info col-sm-12 col-md-10 col-lg-10 col-md-offset-2 col-lg-offset-2">{{{ Session::get('message_fallback') }}}</div>
    <?php Session::forget('message_fallback'); ?>
  <?php endif; ?>

  <div class="row">
    <a class="col-sm-12 col-md-12 col-lg-12" href="{{{ URL::secure('/me/collections') }}}"><< {{ Lang::get('collections.view.back') }}</a>
  </div>

  <div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
      @include('collections.partials.single', array('id' => $collection->id, 'name' => $collection->name, 'isDefault' => $collection->isDefault(), 'count' => $collection->videos->count(), 'isPublic' => $collection->isPublic()))

      @foreach($collection->videos as $i => $video)
        @include('videos.partials.single', array('id' => $video->id, 'poster' => $video->poster, 'embed_url' => $video->embed_url, 'method' => $video->method, 'title' => $video->title, 'duration' => $video->duration, 'index' => $i, 'isNsfw' => $video->isNsfw()))
      @endforeach

    </div>
  </div>

  @include('videos.partials.player')

@stop