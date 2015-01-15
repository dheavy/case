<?php

use GrahamCampbell\Throttle\Facades\Throttle;
use Mypleasure\Services\Validation\User\UserAuthValidator;
use Mypleasure\Services\Validation\User\UserCreateValidator;
use Mypleasure\Services\Validation\User\UserUpdateEmailValidator;
use Mypleasure\Services\Validation\User\UserUpdatePasswordValidator;

class UsersController extends \BaseController {

  protected $user;
  protected $createValidator;
  protected $updateEmailValidator;
  protected $updatePasswordValidator;

  public function __construct(
    User $user,
    UserCreateValidator $createValidator,
    UserUpdateEmailValidator $updateEmailValidator,
    UserUpdatePasswordValidator $updatePasswordValidator)
  {
    $this->user = $user;
    $this->createValidator = $createValidator;
    $this->updateEmailValidator = $updateEmailValidator;
    $this->updatePasswordValidator = $updatePasswordValidator;
  }

  public function index()
  {

  }

  /**
   * Store new User resource.
   *
   * @return Illuminate\Http\RedirectResponse
   */
  public function store()
  {
    // Get ID for "curator" role.
    $defaultRole = Role::where('name', '=', 'curator')->first();

    // Get input with default values, if needed.
    $input = array(
      'username' => Input::get('username', ''),
      'email' => Input::get('email', ''),
      'password' => Input::get('password', ''),
      'password_confirmation' => Input::get('password_confirmation', ''),
      'status' => 0
    );

    // Provide default email address if none was given.
    if (trim($input['email']) === '') {
      $input['email'] = $this->generateDefaultEmail($input['username']);
    }

    // Validate inputs.
    $valid = $this->createValidator->with($input)->passes();

    // Redirect with errors messages, if validation is unsuccessful.
    if (!$valid) {
      return Redirect::route('auth.register')
        ->withErrors($this->createValidator->errors())
        ->withInput(Input::except('password', 'password_confirmation'));
    }

    // Create new User resource.
    $this->user->username = $input['username'];
    $this->user->email = $input['email'];
    $this->user->password = Hash::make($input['password']);
    $this->user->status = $input['status'];
    $this->user->role_id = $defaultRole->id;
    $saved = $this->user->save();

    // Redirect with error message, if save is unsuccessful.
    if (!$saved) {
      return Redirect::to('register')->with('message', 'There was an error while creating your account. Please try again.');
    }

    // Redirect to user profile page.
    return Redirect::route('user.profile');
  }

  public function show($id)
  {

  }

  public function destroy($id)
  {

  }

  public function updatePassword()
  {

  }

  public function updateEmail()
  {

  }

  /**
   * Generate default and fake email address based on provided seed.
   *
   * @param string  $seed   The seed value provided to build fake email upon.
   * @return  string  A fake and default email address.
   */
  protected function generateDefaultEmail($seed)
  {
    $userClass = get_class($this->user);
    return md5($seed) . $userClass::$EMAIL_PLACEHOLDER_SUFFIX;
  }

}
