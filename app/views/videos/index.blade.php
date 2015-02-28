@extends('master')

@section('content')

  @if (Session::has('message'))
  <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @endif

  <div class="row">
    <h3 class="col-sm-12 col-md-12 col-lg-12">
      {{{ $user->username }}}
      {{ Lang::choice('videos.index.pending', $pending, array('count' => (int)$pending)) }}
    </h3>
  </div>

  <div class="row">
    @foreach($collections as $collection)

      @include('collections.partials.single', array('id' => $collection->id, 'name' => $collection->name, 'isDefault' => $collection->isDefault, 'count' => count($collection->videos), 'isPublic' => $collection->isPublic))

      @foreach($collection->videos as $i => $video)
        @include('videos.partials.single', array('id' => $video->id, 'poster' => $video->poster, 'embed_url' => $video->embed_url, 'method' => $video->method, 'title' => $video->title, 'duration' => $video->duration, 'index' => $i))
      @endforeach

    @endforeach
  </div>

  @include('videos.partials.player')

@stop