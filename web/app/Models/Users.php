<?php

/**
 * Class Users
 */
class Users extends \DB\SQL\Mapper {

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

    parent::__construct($db, 'users');
  }

  /**
   * Returns all users.
   *
   * @param $order
   *  Sort order must be string with '<field_name> (asc|desc)'
   * @return object
   */
  public function listAllUsers($order) {
    return $this->select('*', null, ['order' => implode(' ', $order)]);
  }
}