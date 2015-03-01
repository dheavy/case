<?php

return array(

  "login" => array(
    "title" => "Connexion",
    "form" => array(
      "username" => "Identifiant",
      "password" => "Mot de passe",
      "login" => "Se connecter",
      "forgot" => "Mot de passe oublié ?"
    )
  ),

  "register" => array(
    "title" => "Inscription",
    "form" => array(
      "invite" => "Code d'invitation",
      "username" => "Identifiant",
      "email" => "Email",
      "password" => "Mot de passe",
      "passwordconfirmation" => "Confirmer le mot de passe",
      "register" => "S'inscrire"
    )
  ),

  "controller" => array(
    "postLogin" => array(
      "error" => "Identifiant ou mot de passe incorrect."
    ),
    "throttle" => array(
      "error" => "L'authentification a échoué. Merci de réessayer dans :time minutes."
    ),
    "authenticate" => array(
      "error" => "Identifiant ou mot de passe manquant."
    )
  )

);