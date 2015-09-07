<?php

namespace Mypleasure;

use Illuminate\Database\Eloquent\Model;

class Invite extends Model {

  protected $table = 'invites';

  protected $fillable = ['email', 'code', 'from_id'];

}