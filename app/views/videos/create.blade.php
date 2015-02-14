@extends('master')

@section('content')

  <div class="container">
    @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">{{{ $user->username }}} (add video)</h3>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ HTML::ul($errors->all()) }}
    </div>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      <p>To curate a video, enter the URL of its page in the field below.</p>
    </div>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ Form::open(array('url' => URL::secure('/me/videos/create'))) }}

        <div class="form-group">
          {{ Form::label('url', 'Page URL') }}
          {{ Form::text('url', '', array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
          {{ Form::label('collection', 'Add to collection') }}
          <?php $collections[''] = '+ create a new collection'; ?>
          {{ Form::select('collection', $collections, 'new', array('class' => 'form-control', 'id' => 'collection-select')) }}
        </div>

        <div class="form-group hide" id="new-collection-group">
          {{ Form::label('name', 'Name your new collection') }}
          {{ Form::text('name', '', array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
          {{ Form::submit('Add video', array('class' => 'btn btn-primary')) }}
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