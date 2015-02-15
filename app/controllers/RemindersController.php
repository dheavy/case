<?php

/**
 * RemindersController takes care of all task regarding password reset.
 */

class RemindersController extends Controller {

	/**
	 * An instance of the validator for the reminder form.
	 *
	 * @var Mypleasure\Services\Validation\Reminder\RemindPasswordValidator
	 */
	protected $remindValidator;

	/**
	 * An instance of the validator for the reset form.
	 *
	 * @var Mypleasure\Services\Validation\Reminder\ResetPasswordValidator
	 */
	protected $resetValidator;

	/**
	 * Create instance of the controller.
	 *
	 * @param array $validators An array containing an instance validators for both forms used by this controller.
	 */
	public function __construct($validators)
	{
		$this->remindValidator = $validators['remind'];
		$this->resetValidator = $validators['reset'];
	}

	/**
	 * Display the password reminder view.
	 *
	 * @return Response
	 */
	public function getRemind()
	{
		return View::make('password.remind');
	}

	/**
	 * Handle a POST request to remind a user of their password.
	 *
	 * @return Response
	 */
	public function postRemind()
	{
		$valid = $this->remindValidator->with(Input::only('email'))->passes();
		if (!$valid) {
			return Redirect::back()->withErrors($this->remindValidator->errors());
		}

		switch ($response = Password::remind(Input::only('email')))
		{
			case Password::INVALID_USER:
				return Redirect::back()->with('error', Lang::get($response));

			case Password::REMINDER_SENT:
				return Redirect::back()->with('status', Lang::get($response));
		}
	}

	/**
	 * Display the password reset view for the given token.
	 *
	 * @param  string  $token
	 * @return Response
	 */
	public function getReset($token = null)
	{
		if (is_null($token)) App::abort(404);

		return View::make('password.reset')->with('token', $token);
	}

	/**
	 * Handle a POST request to reset a user's password.
	 *
	 * @return Response
	 */
	public function postReset()
	{
		$valid = $this->resetValidator->with(Input::only('email', 'password', 'password_confirmation'))->passes();
		if (!$valid) {
			return Redirect::back()->withErrors($this->resetValidator->errors());
		}

		$credentials = Input::only(
			'email', 'password', 'password_confirmation', 'token'
		);

		$response = Password::reset($credentials, function($user, $password)
		{
			$user->password = Hash::make($password);

			$user->save();
		});

		switch ($response)
		{
			case Password::INVALID_PASSWORD:
			case Password::INVALID_TOKEN:
			case Password::INVALID_USER:
				return Redirect::back()->with('error', Lang::get($response));

			case Password::PASSWORD_RESET:
				return Redirect::to('/');
		}
	}

}
