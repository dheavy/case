<?php

namespace Mypleasure;

use Illuminate\Database\Eloquent\Model;

/**
 * email      {string}
 * code       {string}
 * from_id    {integer|User:id}
 * claimed_at {timestamp}
 * created_at {timestamp}
 */
class Invite extends Model {

  protected $table = 'invites';

  protected $fillable = ['email', 'code', 'from_id'];

  public function sender()
  {
    return $this->belongsTo('\Mypleasure\User', 'from_id', 'id');
  }

}