<?php

use Mypleasure\Services\Validation\User\UserAuthValidator;

/**
 * AuthController deals with authentication related tasks,
 * such as displaying registration and login views, login
 * attempts processing and log outs.
 */

class AuthController extends BaseController {

  /**
   * An instance of the form validator for user authentication.
   *
   * @var Mypleasure\Services\Validation\User\UserAuthValidator
   */
  protected $validator;

  /**
   * The maximum number of login attempts before throttling is enabled.
   *
   * @var integer
   */
  protected $throttleAttemptLimit;

  /**
   * The amount of time before disabling throttling.
   */
  protected $throttleRetentionTime;

  /**
   * Create instance.
   *
   * @param UserAuthValidator $validator              An instance of the validator used for user's authentication.
   * @param integer           $throttleAttemptLimit   The maximum number of login attempts before throttling is enabled.
   * @param integer           $throttleRetentionTime  The amount of time before disabling throttling.
   */
  public function __construct(UserAuthValidator $validator, $throttleAttemptLimit, $throttleRetentionTime)
  {
    parent::__construct();
    $this->validator = $validator;
    $this->throttleAttemptLimit = $throttleAttemptLimit;
    $this->throttleRetentionTime = $throttleRetentionTime;
  }

  /**
   * Display register view.
   * Redirect to user profile if user is already logged in.
   * GET /register
   *
   * @return  Illuminate\View\View|Illuminate\Http\RedirectResponse
   */
  public function getRegister()
  {
    return View::make('auth.register');
  }

  /**
   * Display login view.
   * Redirect to user profile if user is already logged in.
   * GET /login
   *
   * @return  Illuminate\View\View|Illuminate\Http\RedirectResponse
   */
  public function getLogin()
  {
    if (Auth::check()) return Redirect::to('me');
    return View::make('auth.login');
  }

  /**
   * Log user out, then redirect to homepage.
   * GET /logout
   *
   * @return  Illuminate\Http\RedirectResponse
   */
  public function getLogout()
  {
    if (Auth::check()) Auth::logout();
    return Redirect::route('static.home');
  }

  /**
   * Process login form input.
   * POST /login
   *
   * @return  Illuminate\Http\RedirectResponse
   */
  public function postLogin()
  {
    $input = Input::all();

    $this->throttle(Request::getClientIp(), URL::current());

    $credentials = $this->retrieveCredentials($input);

    if (!$credentials) {
      return Redirect::to('login')->with('message', 'Username or password missing.');
    }

    return $this->authenticate($credentials);
  }

  /**
   * Throttles authentication attempts for security.
   *
   * @param string  $ip     Client IP address.
   * @param string  $route  The route to protect.
   */
  protected function throttle($ip, $route)
  {
    // Build Throttler.
    $params = array('ip' => $ip, 'route' => $route);
    $throttler = Throttle::get($params, $this->throttleAttemptLimit, $this->throttleRetentionTime);

    // If attempts numbers are below threshold, we're good.
    $throttler->hit();
    if ($throttler->check()) return;

    // Otherwise, block attemtps for some time and return error message.
    $message = "Authentication failed. Please retry in " . $this->throttleRetentionTime . " minutes.";
    return Redirect::to('login')->with('message', $message);
  }

  /**
   * Extract credentials from user input array.
   *
   * @param   array $input  The user input from a form.
   * @return  array|false
   */
  protected function retrieveCredentials($input)
  {
    if (!array_key_exists('username', $input)
     || !array_key_exists('password', $input)
     || trim($input['username']) === ''
     || trim($input['password']) === '') {
      return false;
    }

    return array(
      'username' => $input['username'],
      'password' => $input['password']
    );
  }

  /**
   * Attempts user authentication based on credentials given.
   *
   * @param array $credentials  The username/password combination.
   * @return Illuminate\Http\RedirectResponse
   */
  protected function authenticate($credentials)
  {
    if (!$this->validator->with($credentials)->passes()) {
      return Redirect::to('login')->withErrors($this->validator->errors());
    }

    if (Auth::attempt($credentials, true)) {
      $user = Auth::user();

      // Check to see if the passwords needs to be re-hashed (when the hash technique is different than when originally saved)?
      if (Hash::needsRehash($user->password)) {
        $user->password = Hash::make(Input::get($credentials['password']));
        $user->save();
      }

      return Redirect::to('me');
    }

    return Redirect::to('login')->with('message', 'Username or password incorrect');
  }

}
