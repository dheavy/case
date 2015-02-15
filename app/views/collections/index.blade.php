@extends('master')

@section('content')

  @if (Session::has('message'))
  <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @endif

  <div class="row">
    <h3 class="col-sm-12 col-md-10 col-lg-10 col-md-offset-2 col-lg-offset-2">
      {{{ $user->username }}} (collections)
    </h3>
  </div>

  <div class="row">
    @foreach($collections as $collection)
    <div class="col-sm-12 col-md-10 col-lg-10 col-md-offset-2 col-lg-offset-2">
      <h4 class="col-sm-12 col-md-12 col-lg-12">Collection "{{ $collection['name'] }}"
        <span>
        @if($collection['numVideos'] <= 1)
          ({{$collection['numVideos']}} video)
        @else
          ({{$collection['numVideos']}} videos)
        @endif
        </span>
[
<a href="<?php echo URL::secure("/me/collections/{$collection['id']}") ?>">view</a> |
<a href="<?php echo URL::secure("/me/collections/{$collection['id']}/edit/") ?>">edit</a>

@if ($collection['isDefault'] === false)
 | <a href="<?php echo URL::secure("/me/collections/{$collection['id']}/delete/") ?>">delete</a>
@endif
]

      </h4>
      <p class="col-sm-12 col-md-12 col-lg-12">
        @if ($collection['status'] === 0)
          This collection is <strong>private</strong>. Its videos <strong>will not appear</strong> to others in <a href="{{{ URL::secure('/feed') }}}">the feed</a>.
        @else
          This collections is <strong>public</strong>. Its videos <strong>will appear</strong> to others in <a href="{{{ URL::secure('/feed') }}}">the feed</a>.
        @endif
      </p>
    </div>
    @endforeach
  </div>

@stop