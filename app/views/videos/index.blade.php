@extends('master')

@section('content')

  <?php if (Session::has('message')): ?>
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    <?php elseif (Session::has('message_fallback')): ?>
      <div class="alert alert-info">{{{ Session::get('message_fallback') }}}</div>
      <?php Session::forget('message_fallback'); ?>
    <?php endif; ?>

  <div class="row">
    <h3 class="col-sm-12 col-md-12 col-lg-12">
      {{{ $user->username }}}
      {{ Lang::choice('videos.index.pending', $pending, array('count' => (int)$pending)) }}
    </h3>
  </div>

  <div class="row videos">
    <?php $index = 0; ?>
    @foreach($collections as $collection)

      @include('collections.partials.single', array('id' => $collection->id, 'name' => $collection->name, 'isDefault' => $collection->isDefault, 'count' => count($collection->videos), 'isPublic' => $collection->isPublic))

      <div class="col-sm-12 col-md-12 col-lg-12">
      @foreach($collection->videos as $video)
        @include('videos.partials.single', array('id' => $video->id, 'poster' => $video->poster, 'embed_url' => $video->embed_url, 'method' => $video->method, 'title' => $video->title, 'duration' => $video->duration, 'isNsfw' => $video->isNsfw(), 'index' => $index))
        <?php $index++; ?>
      @endforeach
      </div>

    @endforeach
  </div>

  @include('videos.partials.player')

@stop