@extends('master')

@section('content')

  @if (Session::has('message'))
  <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @endif

  <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} (videos)</h3>

  <div class="row">

    @foreach($videos as $video)
    <div class="col-sm-12 col-md-3 col-lg-3">
      <img class="col-sm-12 col-md-12 col-lg-12" src="{{ $video->poster }}" width="100%">
      <h5 class="col-sm-12 col-md-12 col-lg-12">{{{ $video->title }}}</h5>
      <div class="col-sm-12 col-md-12 col-lg-12">{{{ $video->duration }}}</div>
    </div>
    @endforeach

    @if ($pending > 0)
      @for ($i = 0; $i < $pending; $i++)
        <div class="col-sm-12 col-md-3 col-lg-3">
          <div class="col-sm-12 col-md-12 col-lg-12" style="background:#CCC">
          <h5 class="col-sm-12 col-md-12 col-lg-12">Processing...</h5>
          <div class="col-sm-12 col-md-12 col-lg-12">(will be available shortly)</div>
        </div>
      @endfor
    @endif

  </div>

@stop