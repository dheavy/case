<?php

use Way\Tests\Should;

class UsersControllersTest extends TestCase {

  public function setUp()
  {
    parent::setUp();
    $this->prepareTestDB();
    $this->createApplication();
  }

}
