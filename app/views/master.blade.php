<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ Lang::get('master.title') }}</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

    <script>
      (function(d) {
        var config = {
          kitId: 'awk3asl',
          scriptTimeout: 3000
        },
        h=d.documentElement,t=setTimeout(function(){h.className=h.className.replace(/\bwf-loading\b/g,"")+" wf-inactive";},config.scriptTimeout),tk=d.createElement("script"),f=false,s=d.getElementsByTagName("script")[0],a;h.className+=" wf-loading";tk.src='//use.typekit.net/'+config.kitId+'.js';tk.async=true;tk.onload=tk.onreadystatechange=function(){a=this.readyState;if(f||a&&a!="complete"&&a!="loaded")return;f=true;clearTimeout(t);try{Typekit.load(config)}catch(e){}};s.parentNode.insertBefore(tk,s)
      })(document);
    </script>

    {{ HTML::style('css/bootstrap.min.css', [], true) }}
    {{ HTML::style('css/bootstrap-theme.min.css', [], true) }}
    {{ HTML::script('js/jquery.min.js', [], true) }}
    {{ HTML::script('js/bootstrap.min.js', [], true) }}
    {{ HTML::script('js/lodash.min.js', [], true) }}

    <style>
    p, a {
      font-size: 14.5px;
    }

    h1, h2, h3, h4, h5, h6 {
      font-family: "futura-pt",sans-serif;
      font-style: normal;
      font-weight: 700;
    }
    </style>

    <!--[if lt IE 9]>
      <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body style="padding-top:70px">
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">{{ Lang::get('master.nav.toggle') }}</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">{{ Lang::get('master.nav.brand') }}</a>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          @if (Auth::check())
            <?php $username = Auth::user()->username; ?>
            <ul class="nav navbar-nav">
              <li><a href="{{{ URL::secure('/me/videos/create') }}}"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true" style="margin-right:10px"></span>{{ Lang::get('master.nav.addvideo') }}</a></li>
              <li><a href="{{{ URL::secure('/feed') }}}"><span class="glyphicon glyphicon-fire" aria-hidden="true" style="margin-right:10px"></span>{{ Lang::get('master.nav.feed') }}</a></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-film" aria-hidden="true" style="margin-right:10px"></span>{{ Lang::get('master.nav.myvideos') }}<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="{{{ URL::secure('/me/videos') }}}">{{ Lang::get('master.nav.allmyvideos') }}</a></li>
                  <li><a href="{{{ URL::secure('/me/collections') }}}">{{ Lang::get('master.nav.seemycollections') }}</a></li>
                  <li><a href="{{{ URL::secure('/me/collections/create') }}}">{{ Lang::get('master.nav.createnewcollections') }}</a></li>
                </ul>
              </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-user" aria-hidden="true" style="margin-right:10px"></span>{{ $username }}<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="{{{ URL::secure('/me') }}}">{{ Lang::get('master.nav.profile') }}</a></li>
                  <li><a href="{{{ URL::secure('/me/edit/email') }}}">{{ Lang::get('master.nav.changeemail') }}</a></li>
                  <li><a href="{{{ URL::secure('/me/edit/password') }}}">{{ Lang::get('master.nav.changepassword') }}</a></li>
                  <li><a href="{{{ URL::secure('/logout') }}}">{{ Lang::get('master.nav.logout') }}</a></li>
                </ul>
              </li>
            </ul>
            @else
              <ul class="nav navbar-nav navbar-right">
                <li><a href="{{{ URL::secure('/register') }}}">{{ Lang::get('master.nav.register') }}</a></li>
                <li><a href="{{{ URL::secure('/login') }}}">{{ Lang::get('master.nav.login') }}</a></li>
              </ul>
            @endif
          </ul>

        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>

    <section class="container">
      @yield('content', 'mypleasu.re')
    </section>
  </body>

</html>
