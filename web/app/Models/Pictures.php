<?php

/**
 * Class Pictures
 */
class Pictures extends \DB\SQL\Mapper {

  /**
   * Pictures constructor.
   */
  public function __construct() {
    // Start up the f3 base class.
    $f3 = Base::instance();

    // Make the db connection.
    $db = new \DB\SQL(
      $f3->get('database'),
      $f3->get('username'),
      $f3->get('password')
    );

    // Initiate the mapper.
    parent::__construct($db, 'pictures');
  }

}