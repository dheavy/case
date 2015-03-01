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
        font-family: "futura-pt",sans-serif;
      }

      h1, h2, h3, h4, h5, h6 {
        font-style: normal;
        font-weight: 700;
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
        background-color: #333;
      }
      body {
        color: #fff;
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
  </body>