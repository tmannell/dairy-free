<?php

/**
 * Class Users
 */
class Tags extends \DB\SQL\Mapper {

  /**
   * Users constructor.
   */
  public function __construct() {
    $f3 = Base::instance();

    $db = new \DB\SQL(
      $f3->get('database'),
      $f3->get('username'),
      $f3->get('password')
    );

    parent::__construct($db, 'tags');
  }

}