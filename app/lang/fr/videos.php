<?php

return array(

  "index" => array(
    "pending" => "{0} (toutes mes vidéos)|[1,Inf] (toutes mes vidéos — :count en cours d'ajout)"
  ),

  "feed" => array(
    "title" => "Flux",
    "subtitle" => "Toutes les vidéos publiques des utilisateurs"
  ),

  "edit" => array(
    "title" => "(modifier video)",
    "form" => array(
      "edittitle" => "Modifier le titre",
      "cancel" => "Annuler",
      "update" => "Mettre à jour"
    )
  ),

  "delete" => array(
    "title" => "(effacer la vidéo)",
    "areyousure" => "Vous êtes sûr de vouloir l'effacer ?",
    "form" => array(
      "cancel" => "Non, annuler",
      "delete" => "Oui, effacer"
    )
  ),

  "create" => array(
    "title" => "(ajouter une vidéo)",
    "instructions" => "Pour ajouter une vidéo, entrez l'adresse de sa page dans le champ ci-dessous.",
    "form" => array(
      "pageurl" => "Adresse de la page",
      "addtocollection" => "Ajouter à la collection",
      "newcollection" => "+ créer une nouvelle collection",
      "nameyourcollection" => "Nommer votre nouvelle collection",
      "addvideo" => "Ajouter la vidéo"
    )
  ),

  "player" => array(
    "title" => "Lecteur vidéo",
    "close" => "Fermer"
  ),

  "single" => array(
    "play" => "Lancer la vidéo",
    "curatedby" => "sélectionné par",
    "editvideo" => "Modifier la vidéo",
    "edittags" => "Voir/modifier les tags",
    "deletevideo" => "Effacer la vidéo"
  ),

  "controller" => array(
    "store" => array(
      "error" => "Oups... Une erreur est survenue lors de l'ajout...",
      "alreadyadded" => "Oups... Cette vidéo a déjà été ajoutée, on dirait !",
      "alreadyprocessing" => "Votre vidéo est déjà en cours d'ajout et apparaîtra dans votre collection dans un instant.",
      "success" => "Votre vidéo apparaîtra dans votre collection dans un instant."
    ),
    "update" => array(
      "error" => "Oups... Une erreur est survenue lors de la mise à jour. Veuillez réessayer.",
      "success" => "Votre vidéo a été mise à jour."
    ),
    "destroy" => array(
      "success" => "Votre vidéo a été effacée."
    )
  )

);