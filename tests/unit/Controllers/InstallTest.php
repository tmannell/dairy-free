<?php

/**
 * Class InstallTest
 *
 * Tests protected methods on the Install controller using reflection.
 */
class InstallTest extends BaseTestCase {

  /**
   * Verifies that addAdminUser inserts a user with a valid password hash.
   *
   * @throws Exception
   */
  public function testAddAdminUser(): void
  {

    $install = new Install();
    $method = new \ReflectionMethod($install, 'addAdminUser');
    $method->invoke($install, 'admin', 'password');

    $result = self::$db->exec('SELECT * FROM users WHERE username = ?', [1 => 'admin']);

    $this->assertSame('admin', $result[0]['username']);
    $this->assertSame(crypt('password', $result[0]['password']), $result[0]['password']);
  }
}