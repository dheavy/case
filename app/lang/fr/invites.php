<?php

return array(

  "controller" => array(
    "store" => array(
      "unauthorized" => "Accès interdit.",
      "userexists" => "L'email est déjà attaché à un compte. L'invitation ne sera pas envoyée.",
      "error" => "Erreur lors de la génération d'invitation, merci de réessayer.",
      "success" => "Invitation envoyé avec succès à :email."
    )
  ),

  "email" => array(
    "subject" => "Invitation MyPleasure / Test Alpha",
    "body" => '
      Salut !
      <br><br>
      Merci d\'avoir accepté de nous aider à tester notre premier prototype de MyPleasure.
      <br><br>
      Juste pour rappel, MyPleasure est une plateforme qui vous permet d\'archiver au même endroit toutes les vidéos qui vous plaisent sur Internet.
      <br><br>
      Pour l\'instant, vous pouvez piocher des vidéos sur ces sites-là :
      - Youtube<br>
      - Vimeo<br>
      - Youporn<br>
      - XVideos<br>
      - XHamster<br>
      ... et pleins d\'autres à venir rapidement.
      <br><br>
      Pour commencer à tester le service, <a href=":url">rendez-vous sur cette page</a> ou copiez/collez le lien suivant dans votre navigateur : <br>:url
      <br><br>
      Et surtout, MERCI (surtout toi mamoune).
      <br><br>
      Morgane & Davy'
  )

);