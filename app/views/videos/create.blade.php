@extends('master')

@section('content')

  <div class="container">
    <?php if (Session::has('message')): ?>
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    <?php elseif (Session::has('message_fallback')): ?>
      <div class="alert alert-info">{{{ Session::get('message_fallback') }}}</div>
      <?php Session::forget('message_fallback'); ?>
    <?php endif; ?>

    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">{{{ $user->username }}} {{ Lang::get('videos.create.title') }}</h3>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ HTML::ul($errors->all()) }}
    </div>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      <p>{{ Lang::get('videos.create.instructions') }}</p>
    </div>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ Form::open(array('url' => URL::secure('/me/videos/create'))) }}

        <div class="form-group">
          {{ Form::label('url', Lang::get('videos.create.form.pageurl')) }}

          @if (isset($u))
            {{ Form::hidden('_extension', true) }}
            {{ Form::text('url', $u, array('class' => 'form-control')) }}
          @elseif (isset($f))
            {{ Form::hidden('_feed', true) }}
            {{ Form::text('url', $f, array('class' => 'form-control')) }}
          @else
            {{ Form::text('url', '', array('class' => 'form-control')) }}
          @endif
        </div>

        <div class="form-group">
          {{ Form::label('collection', Lang::get('videos.create.form.addtocollection')) }}
          <?php $collections[''] = Lang::get('videos.create.form.newcollection'); ?>
          {{ Form::select('collection', $collections, 'new', array('class' => 'form-control', 'id' => 'collection-select')) }}
        </div>

        <div class="form-group hide" id="new-collection-group">
          {{ Form::label('name', Lang::get('videos.create.form.nameyourcollection')) }}
          {{ Form::text('name', '', array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
          {{ Form::submit(Lang::get('videos.create.form.addvideo'), array('class' => 'btn btn-primary')) }}
        </div>
      {{ Form::close() }}
    </div>
  </div>

  <script>
  $(function() {
    var $collectionSelect = $('#collection-select'),
        $newGroup = $('#new-collection-group');

    function newCollectionNameToggle(e) {
      var value = $collectionSelect.val();
      if (value.trim() === '') {
        $newGroup.removeClass('hide');
      } else {
        $newGroup.addClass('hide');
      }
    }

    function adaptUserInterface() {
      if (window.location.href.indexOf('?u=') != -1) {
        $('.navbar').hide(0);
        $('body').css('overflow', 'hidden');
      }
    }

    $collectionSelect.bind('change', newCollectionNameToggle);

    var $window = $(window);
    $window.bind('load', newCollectionNameToggle);
    $window.bind('load', adaptUserInterface);
  });
  </script>

@stop