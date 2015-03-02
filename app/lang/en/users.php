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
  ),

  "profile" => array(
    "delete" => "delete my account",
    "invite" => "generate and send invite",
    "listusers" => "list users",
    "fakevideo" => "[debug] add fake video",
    "infos" => "Informations<small><br>(small changes on the website during alpha test are listed here)</small>",
    "stats" => "Statistics",
    "numcollection" => "{1} You have :count collection, |[2,Inf] You have :count collections, ",
    "numvideos" => "{0} and no videos curated yet. |{1} and curated :count video. |[2, Inf] and curated :count videos."
  ),

  "controller" => array(
    "store" => array(
      "dontmatch" => "Invite code and email don't match.",
      "error" => "There was an error while creating your account. Please try again."
    ),
    "destroy" => array(
      "dontmatch" => "Password does not match, please try again."
    ),
    "updatePassword" => array(
      "invalid" => "Your current password is invalid. Please try again.",
      "error" => "An error occured. Please try again.",
      "success" => "Password updated."
    ),
    "updateEmail" => array(
      "similar" => "No changes were made to your email.",
      "empty" => "No changes were made. Remember you can not to reset a forgotten password without an email.",
      "error" => "Oops... something went wrong! Please try again later.",
      "removed" => "Your email is permanently removed from our database. But remember, you can not to reset a forgotten password without an email.",
      "success" => "Email updated."
    )
  )

);