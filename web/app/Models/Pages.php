<?php

/**
 * Class Pages
 */
class Pages extends \DB\SQL\Mapper {

  /**
   * The database connection
   *
   * @var \DB\SQL
   */
  protected $db;

  /**
   * Pages constructor.
   */
  public function __construct() {
    // Start up the f3 base class.
    $f3 = Base::instance();

    // Connect to the database.
    $this->db = new \DB\SQL(
      $f3->get('database'),
      $f3->get('username'),
      $f3->get('password')
    );

    // Initiate the mapper.
    parent::__construct($this->db, 'pages');
  }

  /**
   * Fine the earliest page created.
   *
   * @return false|mixed
   */
  public function first() {
    $result = $this->select('pid', ['is_published' => 1], [
        'order' => 'pid asc',
        'limit' => 1
      ]
    );

    return $result[0]['pid'];
  }

  /**
   * Find the first page created in the specified tag group.
   *
   * @param $tag
   *
   * @return mixed
   */
  public function firstInGroup($tag) {
    $result = $this->db->exec("
        SELECT t.picture_id
      
        FROM tags t
            LEFT JOIN pictures p ON t.picture_id = p.id

        WHERE p.is_published = 1
            AND t.tag = :tag
        
        ORDER BY p.created_date ASC LIMIT 1 
    ", [':tag' => $tag]);

    return $result[0]['picture_id'];
  }

  /**
   * Find the last page created.
   *
   * @return false|mixed
   */
  public function last() {
    $result = $this->select('pid', ['is_published' => 1], [
        'order' => 'pid desc',
        'limit' => 1
      ]
    );

    return $result[0]['pid'];
  }

  /**
   * Find the last page created in the tag group.
   *
   * @param $tag
   *
   * @return mixed
   */
  public function lastInGroup($tag) {
    $result = $this->db->exec("
        SELECT t.picture_id
      
        FROM tags t
            LEFT JOIN pictures p ON t.picture_id = p.id

        WHERE p.is_published = 1
            AND t.tag = :tag

        ORDER BY p.created_date DESC LIMIT 1 
    ", [':tag' => $tag]);

    return $result[0]['picture_id'];
  }

  /**
   * Go to the next page.
   *
   * @param $created_date
   *
   * @return mixed
   */
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

  /**
   * Go to the next page in the tag group.
   *
   * @param $tag
   * @param $created_date
   *
   * @return mixed
   */
  public function forwardInGroup($tag, $created_date) {
    $result = $this->db->exec("
        SELECT p.pid
      
        FROM pages p
            INNER JOIN tags t ON p.pid = t.picture_id
      
        WHERE created_date > :created_date
            AND is_published = 1
            AND tag = :tag
        
        ORDER BY created_date ASC
      
        LIMIT 1", [':created_date' => $created_date, ':tag' => $tag]
    );

    return $result[0]['pid'];
  }

  /**
   * Go to the previous page.
   *
   * @param $created_date
   *
   * @return mixed
   */
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
   * Go to the previous page in the tag group.
   *
   * @param $tag
   * @param $created_date
   *
   * @return mixed
   */
  public function previousInGroup($tag, $created_date) {
    $result = $this->db->exec("
        SELECT p.pid
      
        FROM pages p
            INNER JOIN tags t ON p.pid = t.picture_id
      
        WHERE created_date < :created_date
            AND is_published = 1
            AND tag = :tag
        
        ORDER BY created_date DESC
      
        LIMIT 1", [':created_date' => $created_date, ':tag' => $tag]
    );

    return $result[0]['pid'];
  }

}