@extends('master')

@section('content')

  <div class="row">
    <h3 class="col-sm-12 col-md-10 col-lg-10 col-md-offset-2 col-lg-offset-2">
      {{{ $user->username }}} {{ Lang::get('collections.index.title') }}
    </h3>

    <?php if (Session::has('message')): ?>
      <div class="alert alert-info col-sm-12 col-md-10 col-lg-10 col-md-offset-2 col-lg-offset-2">{{{ Session::get('message') }}}</div>
    <?php elseif (Session::has('message_fallback')): ?>
      <div class="alert alert-info col-sm-12 col-md-10 col-lg-10 col-md-offset-2 col-lg-offset-2">{{{ Session::get('message_fallback') }}}</div>
      <?php Session::forget('message_fallback'); ?>
    <?php endif; ?>
  </div>

  <div class="row">
    @foreach($collections as $collection)
    <div class="col-sm-12 col-md-10 col-lg-10 col-md-offset-2 col-lg-offset-2">

      @include('collections.partials.single', array('id' => $collection['id'], 'name' => $collection['name'], 'isDefault' => $collection['isDefault'], 'count' => $collection['numVideos'], 'isPublic' => $collection['isPublic']))

      <p class="col-sm-12 col-md-12 col-lg-12">
        @if (!$collection['isPublic'])
          {{ Lang::get('collections.index.public', array('url' => URL::secure('/feed'))) }}
        @else
          {{ Lang::get('collections.index.private', array('url' => URL::secure('/feed'))) }}
        @endif
      </p>
    </div>
    @endforeach
  </div>

@stop