<?php

return array(

  "password" => array(
    "title" => "(modifier le mot de passe)",
    "form" => array(
      "currentpassword" => "Mot de passe actuel",
      "password" => "Nouveau mot de passe",
      "passwordconfirmation" => "Confirmer le nouveau mot de passe",
      "changepassword" => "Changer de mot de passe"
    )
  ),

  "email" => array(
    "title" => "(modifier l'email)",
    "message" => "<h5>Laisser son email n'est pas obligatoire, mais si vous ne le faîtes pas vous ne pourrez pas récupérer votre mot de passe en cas d'oubli !</h5>",
    "form" => array(
      "email" => "Email",
      "update" => "Mettre à jour l'email"
    )
  ),

  "delete" => array(
    "title" => "(détruire le compte)",
    "message" => "<p>Vous nous quittez ? :( C'est dommage... Entrez votre mot de passe dans le champ ci-dessous pour détruire votre compte.</p>",
    "form" => array(
      "password" => "Mot de passe",
      "delete" => "Détruire le compte"
    )
  ),

  "profile" => array(
    "delete" => "détruire mon compte",
    "invite" => "générer et envoyer une invitation",
    "listusers" => "lister les utilisateurs",
    "fakevideo" => "[debug] ajouter un fausse vidéo",
    "infos" => "Informations<small><br>(les modifs du site pendant le test alpha sont listées ici)</small>",
    "stats" => "Statistiques",
    "numcollection" => "{1} Vous avez :count collection, |[2,Inf] Vous avez :count collections, ",
    "numvideos" => "{0} et aucune vidéo ajoutée pour l'instant. |{1} comptant :count vidéo. |[2, Inf] comptant :count vidéos."
  )

);