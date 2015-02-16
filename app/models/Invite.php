<?php

use Carbon\Carbon;

class Invite extends Eloquent {

  /**
   * The mass-assignable attributes in this model.
   *
   * @var array
   */
  protected $fillable = array('code', 'email', 'from_id');

  /**
   * Set claim date on the invite instance, effectively locking it out.
   *
   * @return void
   */
  public function claim()
  {
    $this->claimed_at = Carbon::now()->toDateTimeString();
    $this->save();
  }

}