<?php

return array(

  "view" => array(
    "back" => "Retour aux collections"
  ),

  "index" => array(
    "title" => "(collections)",
    "public" => 'Cette collection est <strong>privée</strong>. Ses vidéos <strong>sont invisibles</strong> aux autres utilisateurs sur <a href=":url">le Flux</a>.',
    "private" => 'Cette collections est <strong>publique</strong>. Ses vidéos <strong>sont visibles</strong> aux autres utilisateurs sur <a href=":url">le Flux</a>.'
  ),

  "edit" => array(
    "title" => '(modifier collection ":name")',
    "form" => array(
      "name" => "Nom",
      "visibility" => "Visibilité",
      "public" => "publique",
      "private" => "privée",
      "cancel" => "Annuler",
      "update" => "Mettre à jour"
    )
  ),

  "delete" => array(
    "title" => "(effacer la collection)",
    "areyousure" => 'Vous êtes sûr de vouloir effacer ":name"?',
    "form" => array(
      "replace" => "On fait quoi de ses vidéos ?",
      "cancel" => "Non, annuler",
      "delete" => "Oui, effacer"
    )
  ),

  "create" => array(
    "title" => "(créer une collection)",
    "form" => array(
      "name" => "Nommez votre nouvelle collection",
      "status" => "Visibilité pour les autres membres du site",
      "public" => "publique",
      "private" => "privée",
      "create" => "Créer la collection"
    )
  ),

  "numvideos" => "{0} Aucune vidéo pour l'instant|{1} (:count vidéo)|[2,Inf] (:count vidéos)",

  "single" => array(
    "title" => "Collection <em>:name</em>",
    "public" => "publique",
    "private" => "privée",
    "info" => "{0} (:status, sans vidéo pour l'instant)|{1} (:status avec :count vidéo)|[2,Inf] (:status avec :count vidéos)",
    "view" => "voir",
    "edit" => "modifier",
    "delete" => "effacer"
  )

);