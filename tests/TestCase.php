<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{

    protected $token;

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
      $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
      return $app;
    }

    public function setUp()
    {
      parent::setUp();
      $this->artisan('migrate:refresh', ['--env' => 'testing']);
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

  protected function signIn()
  {
    if ($this->token) {
      JWTAuth::invalidate($this->token);
    }

    $response = $this->post('api/login', ['username' => 'davy', 'password' => 'azertyuiop']);
    $content = json_decode($this->response->getContent());
    $this->assertObjectHasAttribute('token', $content, 'Token does not exists');
    $this->token = $content->token;

    return $this;
  }
}
