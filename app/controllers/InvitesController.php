<?php

/**
 * InvitesController deals with anything related to registration invite codes.
 */
class InvitesController extends BaseController {

  /**
   * An instance of Invite model.
   *
   * @var Invite
   */
  protected $invite;

  /**
   * Create instance.
   *
   * @param Invite $invite An instance of the Invite model.
   */
  public function __construct(Invite $invite)
  {
    parent::__construct();
    $this->invite = $invite;
  }

  /**
   * Return an invite matching given code and email, if one is found.
   *
   * @param  string $code  The invite code.
   * @param  string $email The recipient's email address.
   * @return Invite|null
   */
  public function getMatchingInvite($code, $email)
  {
    return $this->invite->where('code', '=', $code)
                        ->where('email', '=', $email)
                        ->where('claimed_at', '=', null)
                        ->first();
  }

  /**
   * Generate and save an invite code.
   *
   * @param  string $toEmail The recipient's email address.
   * @param  lixed  $fromId  The ID of the admin who gives away the invite.
   * @return Invite
   */
  public function generate($toEmail, $fromId)
  {
    $data = array(
      'code' => bin2hex(openssl_random_pseudo_bytes(16)),
      'email' => $toEmail,
      'from_id' => $fromId
    );

    return $this->invite->create($data);
  }

}