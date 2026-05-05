<?php

/**
 * Class CronJobsTest
 *
 * Tests the CronJobs controller. Seeds an admin user and two pictures —
 * one with a past publish date and one with a future publish date.
 */
class CronJobsTest extends BaseTestCase {

  /**
   * The seeded admin user ID.
   * @var int
   */
  protected int $uid;

  /**
   * The most recently seeded Pictures mapper instance.
   * @var Pictures
   */
  protected Pictures $pictures;

  /**
   * Seeds a user and two pictures before each test.
   *
   * One picture has a past publish date (should be published by autoPublish),
   * the other has a future publish date (should remain unpublished).
   *
   * @throws Exception
   */
  public function setUp(): void {

    parent::setUp();

    $this->uid = $this->createTestUser('admin', 'admin');

    // Add picture that's published in the past
    $this->createTestPicture('test title pass', date('Y-m-d', strtotime('-1 day')), false, $this->uid);
    $this->createTestPicture('test title fail', date('Y-m-d', strtotime('+1 day')), false, $this->uid);
  }

  /**
   * Verifies that autoPublish sets is_published = 1 for past-dated pictures
   * and leaves future-dated pictures unpublished.
   *
   * @throws Exception
   */
  public function testAutoPublish(): void {

    // Instantiate CronJobs obj.
    $cron = new CronJobs();
    // Call autoPublish.
    $cron->autoPublish();

    // Test unpublished posts that are due.
    $result = self::$db->exec("SELECT * FROM pictures WHERE title = ?", ['test title pass']);
    $this->assertEquals(1, $result[0]['is_published']);

    // Test unpublished posts that are not due.
    $result = self::$db->exec("SELECT * FROM pictures WHERE title = ?", ['test title fail']);
    $this->assertEquals(0, $result[0]['is_published']);
  }
}
