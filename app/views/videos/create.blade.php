@extends('master')

@section('content')

  <div class="container">
    @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

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
          {{ Form::text('url', '', array('class' => 'form-control')) }}
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

    $collectionSelect.bind('change', newCollectionNameToggle);

    $(window).bind('load', newCollectionNameToggle);
  });
  </script>

@stop