<?php

/**
 * Class BaseTestCase
 *
 * Base class for all unit tests. Bootstraps the Fat-Free Framework,
 * establishes the test database connection, creates the schema, and
 * wraps each test in a transaction that is rolled back on teardown
 * to keep the test database clean between runs.
 *
 * F3 config and autoloading are handled by tests/bootstrap.php.
 * This class loads routes and initialises the DB connection via Main.
 *
 * When Phinx migrations are available, replace createDatabase() with
 * a Phinx migrate call in setUpBeforeClass().
 */
class BaseTestCase extends \PHPUnit\Framework\TestCase {

  /**
   * Fat-Free Framework base instance.
   * @var \Base
   */
  protected static \Base $f3;

  /**
   * Shared database connection for all test queries and transactions.
   * @var \DB\SQL
   */
  protected static \DB\SQL $db;

  /**
   * Counter for generating unique picture filenames and created_date values.
   * @var int
   */
  private static int $pictureCount = 0;

  /**
   * Runs once before any tests in the class.
   *
   * Initialises F3, loads routes, establishes the DB connection via Main,
   * and creates the test schema if it does not already exist.
   *
   * @throws Exception
   */
  public static function setUpBeforeClass(): void {

    // Start F3
    self::$f3 = \Base::instance();

    // Instantiate Main to setup DB connection
    new \Main();
    // Put db object in the var.
    self::$db = self::$f3->get('DB');

    // Create database tables
    self::createDatabase();
  }

  /**
   * Runs before each test.
   *
   * Opens a transaction so all DB writes are rolled back in tearDown.
   */
  protected function setUp(): void {
    // Open transaction
    self::$db->begin();
  }

  /**
   * Runs after each test.
   *
   * Rolls back the transaction, resets the picture counter, and resets
   * AUTO_INCREMENT on all tables so each test starts with predictable IDs.
   */
  protected function tearDown(): void {
    // Rollback transaction -- clean db.
    self::$db->rollback();
    self::$pictureCount = 0;
    self::$db->exec('ALTER TABLE users AUTO_INCREMENT = 1');
    self::$db->exec('ALTER TABLE pictures AUTO_INCREMENT = 1');
    self::$db->exec('ALTER TABLE tags AUTO_INCREMENT = 1');
    self::$db->exec('ALTER TABLE media AUTO_INCREMENT = 1');
  }

  /**
   * Creates the test database schema.
   *
   * Uses CREATE TABLE IF NOT EXISTS and CREATE OR REPLACE VIEW so it is safe
   * to call on every test run. Replace with a Phinx migrate call once
   * migrations are in place.
   *
   * @throws Exception
   */
  private static function createDatabase() {

    // Create the database tables.
    self::$db->begin();
    self::$db->exec(
      "CREATE TABLE IF NOT EXISTS dairyfree_test.users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(45) NOT NULL UNIQUE,
                `password` VARCHAR(100) NOT NULL
             ) ENGINE = InnoDB;

             CREATE INDEX IF NOT EXISTS user_id_idx ON dairyfree_test.users (id ASC);"
    );

    self::$db->exec(
      "CREATE TABLE IF NOT EXISTS dairyfree_test.pictures (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(5000) NULL,
                filename VARCHAR(100) NOT NULL UNIQUE,
                user_id INT NOT NULL,
                is_published INT NOT NULL,
                publish_date DATE NOT NULL,
                created_date DATETIME DEFAULT CURRENT_TIMESTAMP UNIQUE,
    
                FOREIGN KEY (user_id)
                REFERENCES dairyfree_test.users (id)
                ON DELETE NO ACTION
                ON UPDATE cascade
             ) ENGINE = InnoDB;

             CREATE INDEX IF NOT EXISTS picture_id_idx ON dairyfree_test.pictures (id ASC);"
    );

    self::$db->exec(
      "CREATE TABLE IF NOT EXISTS dairyfree_test.media (
                id INT AUTO_INCREMENT PRIMARY KEY,
                media_type VARCHAR(45) NOT NULL,
                media VARCHAR(1000) NOT NULL,
                picture_id INT NOT NULL,
      
                FOREIGN KEY (picture_id)
                REFERENCES dairyfree_test.pictures (id)
                ON DELETE CASCADE
                ON UPDATE NO ACTION
             ) ENGINE = InnoDB;

             CREATE INDEX IF NOT EXISTS media_id_idx ON dairyfree_test.media (id ASC);"
    );

    self::$db->exec(
      "CREATE TABLE IF NOT EXISTS dairyfree_test.tags (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tag VARCHAR(45) NOT NULL,
                picture_id INT NOT NULL,
      
                FOREIGN KEY (picture_id)
                REFERENCES dairyfree_test.pictures (id)
                ON DELETE CASCADE
                ON UPDATE NO ACTION
             ) ENGINE = InnoDB;

            CREATE INDEX IF NOT EXISTS tag_id_idx ON dairyfree_test.tags (id ASC);"
    );
    // Create the database.
    self::$db->commit();

    // Now that it's created, lets build a view, for a nice big model.
    self::$db->exec(
      "CREATE OR REPLACE VIEW pages AS
             
             SELECT users.username,
               pictures.id as pid,
               pictures.filename,
               pictures.title,  
               pictures.is_published,
               pictures.publish_date,
               pictures.created_date,
               pictures.user_id, 
               media.id as mid, 
               media.media_type, 
               media.media          
             FROM users
             INNER JOIN pictures on users.id = pictures.user_id
             LEFT JOIN media on pictures.id = media.picture_id"
    );
  }

  /**
   * Creates a test user and returns their ID.
   *
   * @param string $username
   * @param string $password  Plain text password — hashed via User::cryptPassword.
   * @return int  The inserted user's ID.
   * @throws Exception
   */
  protected function createTestUser(string $username, string $password): int {

    // Instantiate user class
    $user = new User();
    // Get hash for passward arg.
    $hash = $user->cryptPassword($password);
    // Insert it and the user into the DB.
    self::$db->exec("INSERT INTO users (username, password) VALUES (?, ?)", [$username, $hash]);

    // Return the new user ID if it's the first user, ID #1 will always be admin. Otherwise reg user.
    return self::$db->lastInsertId();
  }

  /**
   * Creates a test picture and returns its ID.
   *
   * Uses a static counter for filename and created_date to guarantee uniqueness
   * across multiple calls within the same test run.
   *
   * @param string $title
   * @param string $publish_date  Format: Y-m-d
   * @param bool   $is_published
   * @param int    $user_id
   * @return int  The inserted picture's ID.
   */
  protected function createTestPicture(string $title, string $publish_date, bool $is_published, int $user_id): int {

    $pictureCount = ++self::$pictureCount;

    $picture = new Pictures();
    $picture->set('title', $title);
    $picture->set('filename', 'test_file_' . $pictureCount . '.jpg');
    $picture->set('publish_date', $publish_date);
    $picture->set('created_date', '2020-01-' . str_pad($pictureCount, 2, '0', STR_PAD_LEFT) . ' 00:00:00');
    $picture->set('is_published', $is_published);
    $picture->set('user_id', $user_id);

    return $picture->save()->id;
  }

}