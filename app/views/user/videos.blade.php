@extends('master')

@section('content')

  @if (Session::has('message'))
  <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @endif

  <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} (videos)</h3>

  <div class="row">

    @foreach($videos as $video)
    <div class="col-sm-12 col-md-3 col-lg-3 video">
      @if ($video->method === '_dummy')
      <div class="col-sm-12 col-md-12 col-lg-12 dummy" style="display:block;width:100%;height:200px;background:#CCC"></div>
      @else
      <img class="col-sm-12 col-md-12 col-lg-12" src="{{ $video->poster }}" width="100%">
      @endif

      <h5 class="col-sm-12 col-md-12 col-lg-12">{{{ $video->title }}}</h5>
      <div class="col-sm-12 col-md-12 col-lg-12">{{{ $video->duration }}}</div>
      <ul class="col-sm-12 col-md-12 col-lg-12">
        <li><a class="play" href="#">Play video</a></li>
        <li><a class="play" href="#">Edit video</a></li>
        <li><a class="play" href="{{{ URL::route('user.tags.edit', [$video->id]) }}}">View/Edit tags</a></li>
        <li><a class="play" href="#">Delete video</a></li>
      </ul>
    </div>
    @endforeach

    @if ($pending > 0)
      @for ($i = 0; $i < $pending; $i++)
        <div class="col-sm-12 col-md-3 col-lg-3">
          <div class="col-sm-12 col-md-12 col-lg-12" style="display:block;width:100%;height:200px;background:#CCC">
          <h5 class="col-sm-12 col-md-12 col-lg-12">Processing...</h5>
          <div class="col-sm-12 col-md-12 col-lg-12">(will be available shortly)</div>
        </div>
      @endfor
    @endif

  </div>

@stop