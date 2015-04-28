<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase {

	/**
	 * Creates the application.
	 *
	 * @return \Illuminate\Foundation\Application
	 */
	public function createApplication()
	{
		$app = require __DIR__.'/../bootstrap/app.php';

		$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

		return $app;
	}

	/**
   * Set up database for functional testing.
   */
  protected function prepareTestDB()
  {
    Artisan::call('migrate:refresh', ['--env' => 'testing']);
    Artisan::call('migrate', ['--env' => 'testing']);
    Mail::pretend(true);
  }

  /**
   * Generate random string of given length.
   *
   * @param string  $length    The desired length for the string. Defaults to 8.
   * @param string  $useSpaces Include whitespaces in generated string. Defaults to false.
   * @return  string
   */
  protected function generateStringOfLength($length = 8, $useSpaces = false)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if ($useSpaces) $characters.= ' ';
    $string = '';

    for ($i = 0; $i < $length; $i++) {
      $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $string;
  }

}
