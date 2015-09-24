<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost:8000';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
      $app = require __DIR__.'/../bootstrap/app.php';
      $app['Illuminate\Contracts\Console\Kernel']->call('migrate:reset', array('--env' => 'testing'));
      $app['Illuminate\Contracts\Console\Kernel']->call('migrate', array('--env' => 'testing'));
      $app['Illuminate\Contracts\Console\Kernel']->call('db:seed', array('--env' => 'testing'));
      $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

      return $app;
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

  protected function getToken()
  {
    $response = $this->call('POST', 'api/login', ['username' => 'davy', 'password' => 'azertyuiop']);
    return $response->getData()->token;
  }
}
