<?php

return array(

  "view" => array(
    "back" => "Back to collections"
  ),

  "index" => array(
    "title" => "(collections)",
    "public" => 'This collection is <strong>private</strong>. Its videos <strong>will not appear</strong> to others in <a href=":url">the Feed</a>.',
    "private" => 'This collections is <strong>public</strong>. Its videos <strong>will appear</strong> to others in <a href=":url">the Feed</a>.'
  ),

  "edit" => array(
    "title" => '(edit collection ":name")',
    "form" => array(
      "name" => "Name",
      "visibility" => "Visibility",
      "public" => "public",
      "private" => "private",
      "cancel" => "Cancel",
      "update" => "Update collection"
    )
  ),

  "delete" => array(
    "title" => "(delete collection)",
    "areyousure" => 'Are you sure you want to delete ":name"?',
    "form" => array(
      "replace" => "What should we do with the videos in this collection?",
      "cancel" => "No, cancel",
      "delete" => "Yes, delete"
    )
  ),

  "create" => array(
    "title" => "(create collection)",
    "form" => array(
      "name" => "Name your new collection",
      "status" => "Visibility to other site members",
      "public" => "public",
      "private" => "private",
      "create" => "Create collection"
    )
  ),

  "numvideos" => "{0} No videos yet|{1} (:count video)|[2,Inf] (:count videos)",

  "single" => array(
    "title" => "Collection :name",
    "public" => "public",
    "private" => "private",
    "info" => "{0} (:status with no video yet)|{1} (:status with :count video)|[2,Inf] (:status with :count videos)",
    "view" => "view",
    "edit" => "edit",
    "delete" => "delete"
  ),

  "controller" => array(
    "store" => array(
      "error" => "Oops... an error has occured. Please try again.",
      "success" => "Collection created."
    ),
    "update" => array(
      "error" => "Oops... an error has occured. Please try again.",
      "success" => "Collection updated."
    ),
    "getDeleteCollection" => array(
      "replaceSelectList" => "Delete those suckers. FOREVER. BOOM!",
      "moveThemTo" => "Move them to :name"
    ),
    "destroy" => array(
      "success" => "Collection deleted."
    )
  )

);