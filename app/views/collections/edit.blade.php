@extends('master')

@section('content')

  <div class="container">
    @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">{{{ $user->username }}} (edit collection "{{{ $collection->name }}}")</h3>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      {{ HTML::ul($errors->all()) }}
    </div>

    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      <?php $url = URL::secure("/me/collections/" . $collection->id . "/edit") ?>
      {{ Form::open(array('url' => $url)) }}
      {{ Form::hidden('collection', $collection->id) }}
        <div class="form-group">
          {{ Form::label('name', 'Name') }}
          {{ Form::text('name', $collection->name, array('class' => 'form-control')) }}
        </div>

        <div class="form-group">
          <?php $status = $collection->status; ?>
          {{ Form::label('status', 'Visibility') }}
          {{ Form::select('status', array('1' => 'public', '0' => 'private'), $status, array('class' => 'form-control')) }}
        </div>

        {{ Form::submit('Update collection', array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>

@stop