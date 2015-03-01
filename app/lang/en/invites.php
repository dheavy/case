<?php

return array(

  "controller" => array(
    "store" => array(
      "unauthorized" => "Access unauthorized.",
      "userexists" => "User already exists! Invitation not generated.",
      "error" => "Error generating invite. Please try again.",
      "success" => "Invite successfully sent to :email."
    )
  ),

  "email" => array(
    "subject" => "Invite MyPleasure / Alpha test",
    "body" => '
      Hey!
      <br><br>
      Thanks for helping test our first prototype of MyPleasure.
      <br><br>
      A quick reminder — MyPleasure is a platform that allows you to store all the videos you like from the internets in one single place.
      <br><br>
      For now, you may use it on the following websites:
      - Youtube<br>
      - Vimeo<br>
      - Youporn<br>
      - XVideos<br>
      - XHamster<br>
      ...and there are more to come real soon don\'t worry!
      <br><br>
      To start playing with the service, <a href=":url">visit this page</a> or copy/paste the following link in your browser: <br>:url
      <br><br>
      Last but not least... THANK YOU!.
      <br><br>
      Morgane & Davy'
  )

);