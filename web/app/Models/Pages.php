<?php

/**
 * Class Pages
 */
class Pages extends \DB\SQL\Mapper {

  protected $db;

  /**
   * Pages constructor.
   */
  public function __construct() {
    $f3 = Base::instance();

    $this->db = new \DB\SQL(
      $f3->get('database'),
      $f3->get('username'),
      $f3->get('password')
    );

    parent::__construct($this->db, 'pages');
  }

  public function last() {
    $result = $this->select('pid', ['is_published' => 1], [
        'order' => 'pid desc',
        'limit' => 1
      ]
    );

    return $result[0]['pid'];
  }

  public function forward($created_date) {
    $result = $this->db->exec("
      SELECT pid
      
      FROM pages 
      
      WHERE created_date > :created_date
        AND is_published = 1
      
      ORDER BY created_date ASC
      
      LIMIT 1", [':created_date' => $created_date]
    );

    return $result[0]['pid'];
  }

  public function first() {
    $result = $this->select('pid', ['is_published' => 1], [
        'order' => 'pid asc',
        'limit' => 1
      ]
    );

    return $result[0]['pid'];
  }

  public function previous($created_date) {
    $result = $this->db->exec("
      SELECT pid
      
      FROM pages 
      
      WHERE created_date < :created_date
        AND is_published = 1
      
      ORDER BY created_date DESC
      
      LIMIT 1", [':created_date' => $created_date]
    );

    return $result[0]['pid'];
  }

  /**
   * @param bool $published
   * @param $order
   *
   * @return \Pages[]
   */
  public function allPages($order, $published = true) {
    $published = $published ? 'is_published = 1' : null;
    return $this->select('*', $published, ['order' => $order]);
  }
}