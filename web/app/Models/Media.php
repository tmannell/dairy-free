<?php

/**
 * Class Media
 */
class Media extends \DB\SQL\Mapper {

  /**
   * Media constructor.
   */
  public function __construct() {
    // Start up the f3 base class.
    $f3 = Base::instance();

    // Connect to the database.
    $db = new \DB\SQL(
      $f3->get('database'),
      $f3->get('username'),
      $f3->get('password')
    );

    // Initiate the mapper.
    parent::__construct($db, 'media');
  }

}