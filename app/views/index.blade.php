<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ Lang::get('master.title') }}</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

    {{ HTML::style('css/bootstrap.min.css', [], true) }}
    {{ HTML::style('css/bootstrap-theme.min.css', [], true) }}
    {{ HTML::style('css/bigvideo.css', [], true) }}

    <script>
      (function(d) {
        var config = {
          kitId: 'awk3asl',
          scriptTimeout: 3000
        },
        h=d.documentElement,t=setTimeout(function(){h.className=h.className.replace(/\bwf-loading\b/g,"")+" wf-inactive";},config.scriptTimeout),tk=d.createElement("script"),f=false,s=d.getElementsByTagName("script")[0],a;h.className+=" wf-loading";tk.src='//use.typekit.net/'+config.kitId+'.js';tk.async=true;tk.onload=tk.onreadystatechange=function(){a=this.readyState;if(f||a&&a!="complete"&&a!="loaded")return;f=true;clearTimeout(t);try{Typekit.load(config)}catch(e){}};s.parentNode.insertBefore(tk,s)
      })(document);
    </script>

    <style>
      /*
       * Globals
       */
      html, body {
        font-family: "p22-underground",sans-serif;
      }

      h1, h2, h3, h4, h5, h6 {
        font-style: normal;
        font-weight: 400;
      }

      /* Links */
      a,
      a:focus,
      a:hover {
        color: #fff;
      }

      /* Custom default button */
      .btn-default,
      .btn-default:hover,
      .btn-default:focus {
        color: #333;
        text-shadow: none; /* Prevent inheritence from `body` */
        background-color: #fff;
        border: 1px solid #fff;
      }


      /*
       * Base structure
       */

      html,
      body {
        height: 100%;
      }
      body {
        color: #fff;
        background: #333;
        text-align: center;
        text-shadow: 0 1px 3px rgba(0,0,0,.5);
      }

      /* Extra markup and styles for table-esque vertical and horizontal centering */
      .site-wrapper {
        display: table;
        width: 100%;
        height: 100%; /* For at least Firefox */
        min-height: 100%;
        -webkit-box-shadow: inset 0 0 100px rgba(0,0,0,.5);
                box-shadow: inset 0 0 100px rgba(0,0,0,.5);
      }
      .site-wrapper-inner {
        display: table-cell;
        vertical-align: top;
      }
      .cover-container {
        margin-right: auto;
        margin-left: auto;
      }

      /* Padding for spacing */
      .inner {
        padding: 30px;
      }

      .okplayer-mask {
        z-index: 0;
      }

      #okplayer {
        opacity: 0.3;
      }

      .overlay {
        background-repeat: repeat;
        background-size: 5px;
        background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyhpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDE0IDc5LjE1Njc5NywgMjAxNC8wOC8yMC0wOTo1MzowMiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MDZEOTA3MDNCODQ4MTFFNEJFQjhERkRCRDMxODAwMDciIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MDZEOTA3MDRCODQ4MTFFNEJFQjhERkRCRDMxODAwMDciPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDowNkQ5MDcwMUI4NDgxMUU0QkVCOERGREJEMzE4MDAwNyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDowNkQ5MDcwMkI4NDgxMUU0QkVCOERGREJEMzE4MDAwNyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pko6UpUAAAA7SURBVHja7NSxCQAgFEPBKC7u5DqAjcUHEe4tcJAiLcnKXTOF9TwKDAb/D4/qRzI1GAwGg8Fg8NEWYAC4VwI/oadqaQAAAABJRU5ErkJggg==);
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0.7;
        z-index: -1;
      }


      /*
       * Header
       */
      .masthead-brand {
        margin-top: 10px;
        margin-bottom: 10px;
      }

      .masthead-nav > li {
        display: inline-block;
      }
      .masthead-nav > li + li {
        margin-left: 20px;
      }
      .masthead-nav > li > a {
        padding-right: 0;
        padding-left: 0;
        font-size: 16px;
        font-weight: bold;
        color: #fff; /* IE8 proofing */
        color: rgba(255,255,255,.75);
        border-bottom: 2px solid transparent;
      }
      .masthead-nav > li > a:hover,
      .masthead-nav > li > a:focus {
        background-color: transparent;
        border-bottom-color: #a9a9a9;
        border-bottom-color: rgba(255,255,255,.25);
      }
      .masthead-nav > .active > a,
      .masthead-nav > .active > a:hover,
      .masthead-nav > .active > a:focus {
        color: #fff;
        border-bottom-color: #fff;
      }

      @media (min-width: 768px) {
        .masthead-brand {
          float: left;
        }
        .masthead-nav {
          float: right;
        }
      }


      /*
       * Cover
       */

      .cover {
        padding: 0 20px;
      }
      .cover .btn-lg {
        padding: 10px 20px;
        font-weight: bold;
      }
      .cover-heading {
        text-indent: -9999em;
        height: 104px;
        margin-left: auto;
        margin-right: auto;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center;
        background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAl0AAABoCAYAAADLqkUcAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyhpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDE0IDc5LjE1Njc5NywgMjAxNC8wOC8yMC0wOTo1MzowMiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QTJGQTEyNkZCODM2MTFFNDlDOENFQjI2MUYxOUQxQUUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QTJGQTEyNzBCODM2MTFFNDlDOENFQjI2MUYxOUQxQUUiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpBMkZBMTI2REI4MzYxMUU0OUM4Q0VCMjYxRjE5RDFBRSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpBMkZBMTI2RUI4MzYxMUU0OUM4Q0VCMjYxRjE5RDFBRSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmjlcgQAADbFSURBVHja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);
      }


      /*
       * Footer
       */

      .mastfoot {
        color: #999; /* IE8 proofing */
        color: rgba(255,255,255,.5);
      }


      /*
       * Affix and center
       */

      @media (min-width: 768px) {
        /* Pull out the header and footer */
        .masthead {
          position: fixed;
          top: 0;
        }
        .mastfoot {
          position: fixed;
          bottom: 0;
        }
        /* Start the vertical centering */
        .site-wrapper-inner {
          vertical-align: middle;
        }
        /* Handle the widths */
        .masthead,
        .mastfoot,
        .cover-container {
          width: 100%; /* Must be percentage or pixels for horizontal alignment */
        }
      }

      @media (min-width: 992px) {
        .masthead,
        .mastfoot,
        .cover-container {
          width: 700px;
        }
      }
    </style>

    <!--[if lt IE 9]>
      <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
    <div class="overlay"></div>
    <div class="site-wrapper">
      <div class="site-wrapper-inner">
        <div class="cover-container">
          <div class="masthead clearfix">
            <div class="inner">
              <nav>
                <ul class="nav masthead-nav">
                  <li class="active"><a href="{{{ URL::secure('/register') }}}">{{ Lang::get('index.masthead.register') }}</a></li>
                  <li><a href="{{{ URL::secure('/login') }}}">{{ Lang::get('index.masthead.login') }}</a></li>
                </ul>
              </nav>
            </div>
          </div>
          <div class="inner cover">
            <h1 class="cover-heading">mypleasure</h1>
            <p class="lead">{{ Lang::get('index.cover.lead') }}</p>
            {{ Form::open(array('url' => URL::secure('/register'), 'class' => 'col-sm-12 col-md-6 col-lg-6 col-md-offset-3 col-lg-offset-3 form-group-lg')) }}
              {{ Form::hidden('invite', Input::get('c', ''), array('class' => 'form-control')) }}
              {{ Form::hidden('email', Input::get('e', ''), array('class' => 'form-control')) }}
              {{ Form::text('username', Input::old('username'), array('placeholder' => Lang::get('index.cover.form.username'), 'class' => 'form-control', 'style' => 'border-radius: 6px 6px 0 0;')) }}
              {{ Form::password('password', array('placeholder' => Lang::get('index.cover.form.password'), 'class' => 'form-control', 'style' => 'border-radius: 0 0 6px 6px;')) }}
              {{ Form::submit(Lang::get('index.cover.form.register'), array('class' => 'btn btn-lg btn-info col-sm-12 col-md-12 col-lg-12', 'style' => 'margin-top: 1em')) }}
            {{ Form::close() }}
          </div>
        </div>
      </div>
    </div>

    {{ HTML::script('js/jquery.min.js', [], true) }}
    {{ HTML::script('js/bootstrap.min.js', [], true) }}
    {{ HTML::script('js/ie10-viewport-bug-workaround.js', [], true) }}
    {{ HTML::script('js/okvideo.min.js', [], true) }}

    <script>
      $(function(){
        $.okvideo({
          source: 'https://vimeo.com/120972519',
          loop: true
        });
      });
    </script>
  </body>