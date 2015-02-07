@extends('master')

@section('content')

  <div class="container">
    @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} (edit tags for video)</h3>

    <div class="col-sm-12 col-md-3 col-lg-3 video">
      @if ($video->method === '_dummy')
      <div class="col-sm-12 col-md-12 col-lg-12 dummy" style="display:block;width:100%;height:200px;background:#CCC"></div>
      @else
      <img class="col-sm-12 col-md-12 col-lg-12" src="{{ $video->poster }}" width="100%">
      @endif

      <h5 class="col-sm-12 col-md-12 col-lg-12">{{{ $video->title }}}</h5>
    </div>

    <div class="col-sm-12 col-md-12 col-lg-12">
      {{ Form::open(array('url' => $url)) }}

        {{ Form::hidden('video', $video->id) }}
        <div class="form-group">
          {{ Form::label('tags', 'Tags') }}
          <br>
          {{ Form::textarea('tags', $tags) }}
        </div>

        {{ Form::submit('Update tags', array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>

@stop