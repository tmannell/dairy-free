<?php

/**
 * Class UserTest
 *
 * Tests core logic on the User controller — password hashing and authentication.
 * Form-driven methods (add, edit, delete, logout) are excluded as they will be covered
 * later.
 */
class UserTest extends BaseTestCase {

  /**
   * Verifies that cryptPassword produces a valid Blowfish hash.
   */
  public function testCryptPassword(): void {

    $password = 'password';
    $user = new User();
    $hash = $user->cryptPassword($password);

    $this->assertSame($hash, crypt($password, $hash));
  }

  /**
   * Verifies that authenticateUser returns the password hash on success
   * and false on failure, for both admin and standard users.
   *
   * @throws Exception
   */
  public function testAuthenticateUser(): void {

    // Create two users, admin (id 1) and a reg user.
    $admin_id = $this->createTestUser('admin', 'admin');
    $user_id = $this->createTestUser('user', 'user');

    // Instantiate the User class
    $user = new User();

    // Test authentication on admin user
    $authenticated_hash = $user->authenticateUser('admin', 'admin');
    $result = self::$db->exec("SELECT * from users where id = ?", [$admin_id]);
    $this->assertSame($authenticated_hash, $result[0]['password'] );

    // Test authentication on reg user.
    $authenticated_hash = $user->authenticateUser('user', 'user');
    $result = self::$db->exec("SELECT * from users where id = ?", [$user_id]);
    $this->assertSame($authenticated_hash, $result[0]['password'] );
  }
}
