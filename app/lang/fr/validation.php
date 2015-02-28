<?php

return array(

  /*
  |--------------------------------------------------------------------------
  | Validation Language Lines
  |--------------------------------------------------------------------------
  |
  | The following language lines contain the default error messages used by
  | the validator class. Some of these rules have multiple versions such
  | as the size rules. Feel free to tweak each of these messages here.
  |
  */

  "accepted"             => "Le champ :attribute doit être accepté.",
  "active_url"           => "Le champ :attribute n'est pas une URL valide.",
  "after"                => "Le champ :attribute doit être une date après :date.",
  "alpha"                => "Le champ :attribute ne peut contenir que des lettres.",
  "alpha_dash"           => "Le champ :attribute ne peut contenir que lettres, nombres et tirets.",
  "alpha_num"            => "Le champ :attribute ne peut contenir que des lettres et des nombres.",
  "array"                => "Le champ :attribute doit être un tableau.",
  "before"               => "Le champ :attribute doit être une date avant :date.",
  "between"              => array(
    "numeric" => "Le champ :attribute doit être entre :min et :max.",
    "file"    => "Le champ :attribute doit peser :min et :max kb.",
    "string"  => "Le champ :attribute doit avoir entre :min et :max caractères.",
    "array"   => "Le champ :attribute doit contenur entre :min et :max éléments.",
  ),
  "boolean"              => "Le champ :attribute doit être TRUE ou FALSE.",
  "confirmed"            => "Le champ de confirmation de :attribute ne correspond pas à l'original.",
  "date"                 => "Le champ :attribute n'est pas une date valide.",
  "date_format"          => "Le champ :attribute doit être au format : :format.",
  "different"            => "Les champs :attribute et :other doivent être différents.",
  "digits"               => "Le champ :attribute doit contenir :digits chiffres.",
  "digits_between"       => "Le champ :attribute doit contenir entre :min et :max chiffres.",
  "email"                => "Le champ :attribute doit être une adresse email valide.",
  "exists"               => "Le champ :attribute est invalide.",
  "image"                => ":attribute doit être une image.",
  "in"                   => "Le champ :attribute est invalide.",
  "integer"              => "Le champ :attribute doit être un nombre entier.",
  "ip"                   => "Le champ :attribute doit être une adresse IP valide",
  "max"                  => array(
    "numeric" => "Le champ :attribute ne peut pas excéder :max.",
    "file"    => "Le champ :attribute ne peut pas excéder :max kb.",
    "string"  => "Le champ :attribute ne peut pas excéder :max caractères.",
    "array"   => "Le champ :attribute ne peut pas contenir plus de :max éléments.",
  ),
  "mimes"                => ":attribute doit être un fichier parmi les types MIME suivants : :values.",
  "min"                  => array(
    "numeric" => "Le champ :attribute doit être supérieur ou égal à :min.",
    "file"    => "Le champ :attribute doit être supérieur ou égal à :min kb.",
    "string"  => "Le champ :attribute doit avoir au moins :min caractères.",
    "array"   => "Le champ :attribute doit contenir au moins :min éléments.",
  ),
  "not_in"               => "Le champ :attribute est invalide.",
  "numeric"              => "Le champ :attribute doit être un nombre.",
  "regex"                => "Le champ :attribute est invalide.",
  "required"             => "Le champ :attribute est obligatoire.",
  "required_if"          => "Le champ :attribute est obligatoire si :other est :value.",
  "required_with"        => "Le champ :attribute est obligatoire si :values est présent.",
  "required_with_all"    => "Le champ :attribute est obligatoire si :values est présent.",
  "required_without"     => "Le champ :attribute est obligatoire si :values est absent.",
  "required_without_all" => "Le champ :attribute est obligatoire si aucun des champs suivants ne sont présent : :values.",
  "same"                 => "Les champs :attribute et :other doivent correspondre.",
  "size"                 => array(
    "numeric" => "Le champ :attribute doit être égale à :size.",
    "file"    => "Le champ :attribute doit être égale à :size kb.",
    "string"  => "Le champ :attribute doit être égale à :size caractères.",
    "array"   => "Le champ :attribute doit contenir :size éléments.",
  ),
  "unique"               => "Votre choix pour :attribute a déjà été pris avant.",
  "url"                  => "Le format de :attribute est invalide.",
  "timezone"             => ":attribute doit être un fuseau horaire valide.",

  /*
  |--------------------------------------------------------------------------
  | Custom Validation Language Lines
  |--------------------------------------------------------------------------
  |
  | Here you may specify custom validation messages for attributes using the
  | convention "attribute.rule" to name the lines. This makes it quick to
  | specify a specific custom language line for a given attribute rule.
  |
  */

  'custom' => array(
    'attribute-name' => array(
      'rule-name' => 'custom-message',
    ),
  ),

  /*
  |--------------------------------------------------------------------------
  | Custom Validation Attributes
  |--------------------------------------------------------------------------
  |
  | The following language lines are used to swap attribute place-holders
  | with something more reader friendly such as E-Mail Address instead
  | of "email". This simply helps us make messages a little cleaner.
  |
  */

  'attributes' => array(),

);
