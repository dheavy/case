<?php

return array(

  "login" => array(
    "title" => "Log in",
    "form" => array(
      "username" => "Username",
      "password" => "Password",
      "login" => "Log in",
      "forgot" => "Forgotten password?"
    )
  ),

  "register" => array(
    "title" => "Register",
    "form" => array(
      "invite" => "Invite code",
      "username" => "Username",
      "email" => "Email",
      "password" => "Password",
      "passwordconfirmation" => "Confirm password",
      "register" => "Register"
    )
  ),

  "controller" => array(
    "postLogin" => array(
      "error" => "Username or password missing."
    ),
    "throttle" => array(
      "error" => "Authentication failed. Please retry in :time minutes."
    ),
    "authenticate" => array(
      "error" => "Username or password missing."
    )
  )

);