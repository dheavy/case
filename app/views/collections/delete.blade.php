@extends('master')

@section('content')

  <div class="row">
    <h3 class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">{{{ $user->username }}} {{ Lang::get('collections.delete.title') }}</h3>

    @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    <?php $url = URL::secure("/me/collections/{$collection->id}/delete"); ?>

    {{ Form::open(array('url' => $url)) }}
    {{ Form::hidden('collection', $collection->id) }}
    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      <h4>{{ Lang::get('collections.delete.areyousure', array('name' => $collection->name)) }}</h4>

      <?php if ($hasVideos): ?>
      <div class="form-group">
        {{ Form::label('replace', Lang::get('collections.delete.form.replace')) }}
        {{ Form::select('replace', $replaceSelectList, null, array('class' => 'form-control')) }}
      </div>
      <?php endif; ?>

      <div class="form-group">
        <a href="{{{ URL::secure('/me/collections') }}}" class="btn btn-default">{{ Lang::get('collections.delete.form.cancel') }}</a>
        <input type="submit" class="btn btn-primary" value="{{ Lang::get('collections.delete.form.delete') }}">
      </div>
    </div>
  {{ Form::close() }}

  </div>

@stop