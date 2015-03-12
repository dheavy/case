@extends('master')

@section('content')

  <div class="row">
    <div class="col-sm-12 col-md-10 col-lg-10 col-md-offset-2 col-lg-offset-2" style="text-align:center">
      @if (Session::has('message'))
        <h3>{{{ Session::get('message') }}}</h3>
      @endif
    </div>
  </div>

  <script>
    $(window).bind('load', function adaptUserInterface() {
      $('.navbar').hide(0);
    });

    _.delay(window.close, 4000);
  </script>

@stop