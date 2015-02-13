<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>mypleasu.re</title>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

    {{ HTML::style('css/bootstrap.min.css', [], true) }}
    {{ HTML::style('css/bootstrap-theme.min.css', [], true) }}
    {{ HTML::script('js/jquery.min.js', [], true) }}
    {{ HTML::script('js/bootstrap.min.js', [], true) }}
    {{ HTML::script('js/lodash.min.js', [], true) }}

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
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">mypleasu.re</a>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          @if (Auth::check())
            <?php $username = Auth::user()->username; ?>
            <ul class="nav navbar-nav">
              <li><a href="{{{ URL::secure('/me/videos/create') }}}"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true" style="margin-right:10px"></span>add video</a></li>
              <li><a href="{{{ URL::secure('/feed') }}}"><span class="glyphicon glyphicon-fire" aria-hidden="true" style="margin-right:10px"></span>feed</a></li>
              <li><a href="{{{ URL::secure('/me/videos') }}}"><span class="glyphicon glyphicon-film" aria-hidden="true" style="margin-right:10px"></span>my videos</a></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-th-large" aria-hidden="true" style="margin-right:10px"></span>my collections<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="{{{ URL::secure('/me/collections') }}}">see my collections</a></li>
                  <li><a href="{{{ URL::secure('/me/collections/create') }}}">create new collection</a></li>
                </ul>
              </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-user" aria-hidden="true" style="margin-right:10px"></span>{{ $username }}<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="{{{ URL::secure('/me') }}}">profile</a></li>
                  <li><a href="{{{ URL::secure('/me/edit/email') }}}">change/remove email</a></li>
                  <li><a href="{{{ URL::secure('/me/edit/password') }}}">change password</a></li>
                  <li><a href="{{{ URL::secure('/logout') }}}">log out</a></li>
                </ul>
              </li>
            </ul>
            @else
              <ul class="nav navbar-nav navbar-right">
                <li><a href="{{{ URL::secure('/register') }}}">register</a></li>
                <li><a href="{{{ URL::secure('/login') }}}">sign in</a></li>
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
