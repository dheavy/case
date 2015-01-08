<?php

use Mypleasure\Services\Validation\User\UserUpdateValidator;
use Way\Tests\Should;

class UserUpdateEmailValidatorTest extends TestCase {

  public function setUp()
  {
    parent::setUp();
    $this->prepareTestDB();
    $this->createApplication();
    $this->validator = App::make('UserUpdateEmailValidator');
  }

  public function testEmailFails()
  {
    $input = array(
      'email' => 'goku @example.com'
    );

    Should::beFalse($this->validator->with($input)->passes());

    $input = array(
      'email' => 'goku'
    );

    Should::beFalse($this->validator->with($input)->passes());

    $input = array(
      'email' => 'goku@example@sss.com'
    );

    Should::beFalse($this->validator->with($input)->passes());
  }

  public function testEmailPasses()
  {
    $input = array(
      'email' => 'goku@example.com'
    );

    Should::beTrue($this->validator->with($input)->passes());
  }

}
