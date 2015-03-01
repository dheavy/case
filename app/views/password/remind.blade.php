@extends('master')

@section('content')

  <div class="row">
    <div class="col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3">
      @if (Session::has('status'))
        <div class="alert alert-info">{{{ Session::get('status') }}}</div>
      @elseif (Session::has('error'))
        <div class="alert alert-info">{{{ Session::get('error') }}}</div>
      @endif

      <h3 class="col-sm-12 col-md-12 col-lg-12">{{ Lang::get('reminders.page.title') }}</h3>

      <div class="col-sm-12 col-md-12 col-lg-12">
        {{ HTML::ul($errors->all()) }}
      </div>

      <div class="col-sm-12 col-md-12 col-lg-12">
        <p>{{ Lang::get('reminders.page.message') }}</p>
      </div>

      <div class="col-sm-12 col-md-12 col-lg-12">
        {{ Form::open(array('action' => 'RemindersController@postRemind')) }}

          <div class="form-group">
            {{ Form::label('email', Lang::get('reminders.page.form.email')) }}
            {{ Form::email('email', '', array('class' => 'form-control')) }}
          </div>

        {{ Form::submit(Lang::get('reminders.page.form.send'), array('class' => 'btn btn-primary')) }}
      </div>
    </div>
  </div>

@stop