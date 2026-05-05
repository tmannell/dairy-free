<?php

/**
 * Class PageTest
 *
 * Tests protected methods on the Page controller using reflection.
 */
class PageTest extends BaseTestCase {

  /**
   * The seeded test picture ID.
   * @var int
   */
  protected int $picture_id;

  /**
   * Seeds an admin user and a single published picture before each test.
   *
   * @throws Exception
   */
  public function setUp(): void {
    parent::setUp();

    $user_id = $this->createTestUser('admin', 'admin');
    $this->picture_id = $this->createTestPicture('test_picture', date('Y-m-d', strtotime('-1 days')), true, $user_id);
  }

  /**
   * Verifies that saveTags() correctly adds, updates, and removes tags for a picture.
   *
   * @throws ReflectionException
   */
  public function testSaveTags(): void {

    $page = new Page();
    $method = new \ReflectionMethod($page, 'saveTags');

    // Add two tags.
    $method->invoke($page, 'foo, bar', $this->picture_id);
    $tags = $this->getTagsForPicture($this->picture_id);
    $this->assertCount(2, $tags);
    $this->assertContains('foo', $tags);
    $this->assertContains('bar', $tags);

    // Add a third tag.
    $method->invoke($page, 'foo, bar, baz', $this->picture_id);
    $tags = $this->getTagsForPicture($this->picture_id);
    $this->assertCount(3, $tags);
    $this->assertContains('foo', $tags);
    $this->assertContains('bar', $tags);
    $this->assertContains('baz', $tags);

    // Replace tags — bar removed, one added.
    $method->invoke($page, 'one, baz', $this->picture_id);
    $tags = $this->getTagsForPicture($this->picture_id);
    $this->assertCount(2, $tags);
    $this->assertContains('one', $tags);
    $this->assertContains('baz', $tags);
    $this->assertNotContains('bar', $tags);

    // Empty string clears all tags.
    $method->invoke($page, '   ', $this->picture_id);
    $tags = $this->getTagsForPicture($this->picture_id);
    $this->assertEmpty($tags);
  }

  /**
   * Returns a flat array of tag names for the given picture ID.
   *
   * @param int $picture_id
   * @return array
   *
   * @throws Exception
   */
  private function getTagsForPicture(int $picture_id): array {
    $results = self::$db->exec("SELECT * FROM tags WHERE picture_id = ?", [$picture_id]);
    return array_column($results, 'tag');
  }
}