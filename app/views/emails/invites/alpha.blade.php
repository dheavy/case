<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
  </head>
  <body>

    {{ Lang::get('invites.email.body', array('url' => URL::secure($url))) }}

  </body>
</html>
