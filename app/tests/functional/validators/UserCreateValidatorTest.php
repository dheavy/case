<?php

use Mypleasure\Services\Validation\User\UserCreateValidator;
use Way\Tests\Should;

/**
 * UserCreateValidatorTest tests UserCreateValidator.
 */

class UserCreateValidatorTest extends TestCase {

  public function setUp()
  {
    parent::setUp();
    $this->prepareTestDB();
    $this->createApplication();
    $this->validator = App::make('UserCreateValidator');
  }

  public function testUsernameFails()
  {
    $input = array(
      'username' => ''
    );

    Should::beFalse($this->validator->with($input)->passes());
    Should::contain('The username field is required.', $this->validator->errors()->get('username')[0]);
    $input = array(
      'username' => 'son goku'
    );

    Should::beFalse($this->validator->with($input)->passes());
    Should::contain('The username may only contain letters and numbers.', $this->validator->errors()->get('username')[0]);

    $input = array(
      'username' => 'a'
    );

    Should::beFalse($this->validator->with($input)->passes());
    Should::contain('The username must be between 2 and 25 characters.', $this->validator->errors()->get('username')[0]);

    $input = array(
      'username' => $this->generateStringOfLength(26)
    );

    Should::beFalse($this->validator->with($input)->passes());
    Should::contain('The username must be between 2 and 25 characters.', $this->validator->errors()->get('username')[0]);
  }

  public function testUsernamePasses()
  {
    $input = array(
      'username' => $this->generateStringOfLength(10)
    );

    Should::beTrue($this->validator->with($input)->passes());
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

  public function testPasswordFails()
  {
    $pwd = $this->generateStringOfLength(10);

    $input = array(
      'password' => $pwd,
      'password_confirmation' => 'not_the_same'
    );

    Should::beFalse($this->validator->with($input)->passes());
    Should::contain('The password confirmation does not match.', $this->validator->errors()->get('password')[0]);

    $input = array(
      'password' => $pwd . ' a',
      'password_confirmation' => $pwd . ' a'
    );

    Should::beFalse($this->validator->with($input)->passes());
    Should::contain('The password may only contain letters and numbers.', $this->validator->errors()->get('password')[0]);

    $input = array(
      'password' => 'azert',
      'password_confirmation' => 'azert'
    );

    Should::beFalse($this->validator->with($input)->passes());
    Should::contain('The password must be between 6 and 16 characters.', $this->validator->errors()->get('password')[0]);

    $tooLong = $this->generateStringOfLength(17);
    $input = array(
      'password' => $tooLong,
      'password_confirmation' => $tooLong
    );

    Should::beFalse($this->validator->with($input)->passes());
    Should::contain('The password must be between 6 and 16 characters.', $this->validator->errors()->get('password')[0]);
  }

  public function testPasswordPasses()
  {
    $pwd = $this->generateStringOfLength(10);

    $input = array(
      'password' => $pwd,
      'password_confirmation' => $pwd
    );
    Should::beTrue($this->validator->with($input)->passes());
  }

}
