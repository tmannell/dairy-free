<?php

/**
 * Class PagesTest
 *
 * Tests navigation and query methods on the Pages model.
 * Seeds six pictures — a mix of published/unpublished and tagged/untagged —
 * to exercise first, last, forward, previous, group, random, and total queries.
 */
class PagesTest extends BaseTestCase {

  /**
   * First tag name used across seeded pictures.
   * @var string
   */
  protected string $tag_one;

  /**
   * Second tag name used across seeded pictures.
   * @var string
   */
  protected string $tag_two;

  /**
   * Seeds an admin user and six pictures with varying publish dates,
   * published states, and tags before each test.
   *
   * Picture order by created_date: one, two, three, four, five, six.
   * Published: one, three, four, five. Unpublished: two, six.
   * Tags: three → test_1, four → test_2, five → test_1 + test_2.
   *
   * @throws Exception
   */
  public function setUp(): void {
    parent::setUp();

    // Define tags.
    $this->tag_one = 'bananas';
    $this->tag_two = 'coconuts';

    // Create test user.
    $user_id = $this->createTestUser('admin', 'admin');

    // Page data.
    $pages = [
      ['title' => 'picture one', 'publish_date' => date('Y-m-d', strtotime('-5 days')), 'is_published' => true, 'user_id' => $user_id, 'tags' => []],
      ['title' => 'picture two', 'publish_date' => date('Y-m-d', strtotime('-4 days')), 'is_published' => false, 'user_id' => $user_id, 'tags' => []],
      ['title' => 'picture three', 'publish_date' => date( 'Y-m-d', strtotime('-3 days')), 'is_published' => true, 'user_id' => $user_id, 'tags' => [$this->tag_one]],
      ['title' => 'picture four', 'publish_date' => date( 'Y-m-d', strtotime('-2 days')), 'is_published' => true, 'user_id' => $user_id, 'tags' => [$this->tag_two]],
      ['title' => 'picture five', 'publish_date' => date( 'Y-m-d', strtotime('-1 days')), 'is_published' => true, 'user_id' => $user_id, 'tags' => [$this->tag_one, $this->tag_two]],
      ['title' => 'picture six', 'publish_date' => date( 'Y-m-d', strtotime('+1 days')), 'is_published' => false, 'user_id' => $user_id, 'tags' => []]
    ];

    // Loop through page data and create pages.
    foreach ($pages as $page) {
      // Add picture.
      $picture_id = $this->createTestPicture($page['title'], $page['publish_date'], $page['is_published'], $page['user_id']);
      // Add page tags.
      foreach ($page['tags'] as $new_tag) {
        $tag = new Tags();
        $tag->set('tag', $new_tag);
        $tag->set('picture_id', $picture_id);
        $tag->save();
      }
    }
  }

  /**
   * Verifies that first() returns the earliest published picture.
   *
   * @throws Exception
   */
  public function testFirst(): void {
    $pages = new Pages();
    $picture_id = $pages->first();
    $page = self::$db->exec('SELECT pictures.id FROM pictures WHERE is_published = true ORDER BY created_date ASC LIMIT 1');

    $this->assertSame($page[0]['id'], $picture_id);
  }

  /**
   * Verifies that firstInGroup() returns the earliest picture in a tag group.
   *
   * @throws Exception
   */
  public function testFirstInGroup(): void {
    $pages = new Pages();
    $picture_id = $pages->firstInGroup($this->tag_one);
    $page = self::$db->exec('SELECT t.picture_id FROM tags t
                                     LEFT JOIN pictures p on t.picture_id = p.id
                                   WHERE t.tag = ? AND p.is_published = true 
                                   ORDER BY created_date ASC LIMIT 1', [$this->tag_one]);

    $this->assertSame($page[0]['picture_id'], $picture_id);
  }

  /**
   * Verifies that last() returns the most recently published picture.
   *
   * @throws Exception
   */
  public function testLast(): void {
    $pages = new Pages();
    $picture_id = $pages->last();
    $page = self::$db->exec('SELECT id FROM pictures WHERE is_published = true ORDER BY created_date DESC LIMIT 1');

    $this->assertSame($page[0]['id'], $picture_id);
  }

  /**
   * Verifies that lastInGroup() returns the most recent picture in a tag group.
   *
   * @throws Exception
   */
  public function testLastInGroup(): void {
    $pages = new Pages();
    $picture_id = $pages->lastInGroup($this->tag_two);
    $page = self::$db->exec('SELECT t.picture_id FROM tags t
                                     LEFT JOIN pictures p on t.picture_id = p.id
                                   WHERE t.tag = ? AND p.is_published = true 
                                   ORDER BY created_date DESC LIMIT 1', [$this->tag_two]);

    $this->assertSame($page[0]['picture_id'], $picture_id);
  }

  /**
   * Verifies that forward() returns the next published picture by created_date.
   *
   * @throws Exception
   */
  public function testForward(): void {
    $pictures = self::$db->exec('SELECT id, created_date FROM pictures WHERE is_published = true ORDER BY created_date ASC LIMIT 2');

    $pages = new Pages();
    $picture_id = $pages->forward($pictures[0]['created_date']);

    $this->assertSame($pictures[1]['id'], $picture_id);
  }

  /**
   * Verifies that forwardInGroup() returns the next published picture in a tag group.
   *
   * @throws Exception
   */
  public function testForwardInGroup(): void {
    $pictures = self::$db->exec('SELECT p.id, p.created_date, t.tag FROM pictures as p 
                                        LEFT JOIN tags as t ON p.id = t.picture_id 
                                      WHERE is_published = true AND t.tag = ?
                                      ORDER BY created_date ASC LIMIT 2 ', [$this->tag_one]);
    $pages = new Pages();
    $picture_id = $pages->forwardInGroup($pictures[0]['tag'], $pictures[0]['created_date']);

    $this->assertSame($pictures[1]['id'], $picture_id);
  }

  /**
   * Verifies that previous() returns the prior published picture by created_date.
   *
   * @throws Exception
   */
  public function testPrevious(): void {
    $pictures = self::$db->exec('SELECT id, created_date FROM pictures WHERE is_published = true ORDER BY created_date DESC LIMIT 2');
    $pages = new Pages();

    $picture_id = $pages->previous($pictures[0]['created_date']);

    $this->assertSame($pictures[1]['id'], $picture_id);
  }

  /**
   * Verifies that previousInGroup() returns the prior picture in a tag group.
   *
   * @throws Exception
   */
  public function testPreviousInGroup(): void {
    $pictures = self::$db->exec('SELECT p.id, p.created_date, t.tag FROM pictures as p 
                                        LEFT JOIN tags as t ON p.id = t.picture_id 
                                       WHERE is_published = true AND t.tag = ?
                                       ORDER BY created_date DESC LIMIT 2 ', [$this->tag_one]);
    $pages = new Pages();
    $picture_id = $pages->previousInGroup($this->tag_one, $pictures[0]['created_date']);

    $this->assertSame($pictures[1]['id'], $picture_id);
  }

  /**
   * Verifies that randomPage() returns a valid published picture ID.
   *
   * @throws Exception
   */
  public function testRandomPage(): void {
    $pages = new Pages();
    $picture_id = $pages->randomPage();

    $result = self::$db->exec('SELECT * FROM pictures WHERE id = ?', [$picture_id]);
    $this->assertNotEmpty($result);
    $this->assertEquals(1, $result[0]['is_published']);
  }

  /**
   * Verifies that totalPages() returns the count of published pictures only.
   *
   * @throws Exception
   */
  public function testTotalPages(): void {
    $pages = new Pages();
    $total = $pages->totalPages();

    $result = self::$db->exec('SELECT COUNT(*) as total FROM pictures WHERE is_published = true');

    $this->assertSame((int) $result[0]['total'], $total);
  }

  /**
   * Verifies that newest() returns the most recently created published picture.
   *
   * @throws Exception
   */
  public function testNewest(): void {
    $pages = new Pages();
    $picture_id = $pages->newest();
    $picture = self::$db->exec('SELECT id FROM pictures WHERE is_published = true ORDER BY created_date DESC LIMIT 1');

    $this->assertSame($picture[0]['id'], $picture_id);
  }

}
