@extends('master')

@section('content')

  <div class="row">
    <div class="col-sm-12 col-md-10 col-lg-10 col-md-offset-2 col-lg-offset-2" style="text-align:center">
      <?php if (Session::has('message')): ?>
        <div class="alert alert-info">{{{ Session::get('message') }}}</div>
      <?php elseif (Session::has('message_fallback')): ?>
        <div class="alert alert-info">{{{ Session::get('message_fallback') }}}</div>
        <?php Session::forget('message_fallback'); ?>
      <?php endif; ?>
    </div>
  </div>

  <script>
    $(window).bind('load', function adaptUserInterface() {
      $('.navbar').hide(0);
    });
  </script>

@stop