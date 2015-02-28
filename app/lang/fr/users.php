<?php

return array(

  "password" => array(
    "title" => "(edit password)",
    "form" => array(
      "currentpassword" => "Current Password",
      "password" => "New password",
      "passwordconfirmation" => "Confirm new password",
      "changepassword" => "Change password"
    )
  ),

  "email" => array(
    "title" => "(edit email)",
    "message" => '<h5>Email address is not mandatory, but without it you can not retrieve your forgotten password.</h5>',
    "form" => array(
      "email" => "Email",
      "update" => "Update email address"
    )
  ),

  "delete" => array(
    "title" => "(delete account)",
    "message" => "<p>We're sorry to see you go... Enter your password below to delete your account.</p>",
    "form" => array(
      "password" => "Password",
      "delete" => "Delete account"
    )
  )

);