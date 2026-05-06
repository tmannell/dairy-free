<?php

/**
 * Class HelperTest
 *
 * Tests static utility methods on the Helper class.
 */
class HelperTest extends BaseTestCase {

  /**
   * Verifies that explodePath() returns all path components as an array,
   * and returns a single component when a key is specified.
   */
  public function testExplodePath(): void {

    self::$f3->set('PATH', "page/25/edit");

    $args = \Helper::explodePath();
    $this->assertIsArray($args);
    $this->assertSame('page', $args[0]);
    $this->assertSame('25', $args[1]);
    $this->assertSame('edit', $args[2]);

    self::$f3->set('PATH', "page/13/delete");
    $arg = \Helper::explodePath(1);
    $this->assertEquals('13', $arg);
  }
}
