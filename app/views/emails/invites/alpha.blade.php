<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
  </head>
  <body>
    <h2>Hello There! Salut !</h2>

    <div>
      Register to <strong>mypleasu.re (alpha)</strong> with your email address.<br><br><br>
      <a href="{{ URL::secure($url) }}">Click here to complete to registration form</a>, or copy and paste the link below in a new browser window:<br>
      {{ URL::secure($url) }}
    </div>

  </body>
</html>
