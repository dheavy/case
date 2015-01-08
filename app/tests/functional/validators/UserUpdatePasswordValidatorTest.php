<?php

use Mypleasure\Services\Validation\User\UserUpdateValidator;
use Way\Tests\Should;

class UserUpdatePasswordValidatorTest extends TestCase {

  public function setUp()
  {
    parent::setUp();
    $this->prepareTestDB();
    $this->createApplication();
    $this->validator = App::make('UserUpdatePasswordValidator');
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
