<?php

use Illuminate\Mail\Mailer;
use Mypleasure\Services\Validation\Invite\InviteValidator;

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

  protected $validator;

  /**
   * Create instance.
   *
   * @param Invite $invite An instance of the Invite model.
   */
  public function __construct(Invite $invite, InviteValidator $validator)
  {
    parent::__construct();
    $this->invite = $invite;
    $this->validator = $validator;
  }

  public function getCreateInvite()
  {
    return View::make('admin.invites.create')->with('user', Auth::user());
  }

  /**
   * Return an invite matching given code and email, if one is found.
   *
   * @param  string $code  The invite code.
   * @param  string $email The recipient's email address.
   * @return Invite|null
   */
  public function fetchMatchingInvite($code, $email)
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

  public function store()
  {
    // Get admin user and reject if not found.
    $admin = Auth::user();
    if (!$admin->role->name === 'admin') App::abort(401, Lang::get('invites.controller.store.unauthorized'));

    // Get and validate form input.
    $email = trim(strtolower(Input::get('email', '')));
    $valid = $this->validator->with(array('email' => $email))->passes();
    if (!$valid) return Redirect::back()->withErrors($this->validator->errors());

    // Ensure recipient is not already a user.
    $existingUser = User::where('email', '=', $email)->first();
    if ($existingUser) return Redirect::back()->with('message', Lang::get('invites.controller.store.userexists'));

    // Generate invite.
    $invite = $this->generate($email, $admin->id);
    if (!$invite) return Redirect::back()->with('message', Lang::get('invites.controller.store.error'));

    // Send invite to recipient and redirect with short message.
    $this->sendInvite($invite);
    return Redirect::back()->with('message', Lang::get('invites.controller.store.success', array('email' => $email)));
  }

  protected function sendInvite($invite)
  {
    $view = 'emails.invites.alpha';
    $path = "/register?c={$invite->code}&e={$invite->email}";

    Mail::send('emails.invites.alpha', array('url' => $path), function($message) use (&$invite) {
      $message->to($invite->email)->subject(Lang::get('invites.email.subject'));
    });
  }

}