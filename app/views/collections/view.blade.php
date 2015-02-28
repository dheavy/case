@extends('master')

@section('content')

  @if (Session::has('message'))
  <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @endif

  <div class="row">
    <a class="col-sm-12 col-md-12 col-lg-12" href="{{{ URL::secure('/me/collections') }}}"><< {{ Lang::get('collections.view.back') }}</a>
  </div>

  <div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
      @include('collections.partials.single', array('id' => $collection->id, 'name' => $collection->name, 'isDefault' => $collection->isDefault(), 'count' => $collection->videos->count(), 'isPublic' => $collection->isPublic()))

      @foreach($collection->videos as $i => $video)
        @include('videos.partials.single', array('id' => $video->id, 'poster' => $video->poster, 'embed_url' => $video->embed_url, 'method' => $video->method, 'title' => $video->title, 'duration' => $video->duration, 'index' => $i))
      @endforeach

    </div>
  </div>

  @include('videos.partials.player')

@stop