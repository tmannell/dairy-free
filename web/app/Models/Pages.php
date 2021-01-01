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
   * Where statement chunk.
   *
   * @var string
   */
  protected $is_published;

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

    // Create a where statement chunk, whether a user can view published
    // or unpublished pages differs between roles.
    $main = new Main();
    if (in_array($main->getAuthorizationStatus(), ['admin', 'authorized'], true)) {
      // Find everything.
      $this->is_published = 'is_published IS NOT NULL';
    }
    else {
      // Find only published.
      $this->is_published = 'is_published = 1';
    }

    // Initiate the mapper.
    parent::__construct($this->db, 'pages');
  }

  /**
   * Fine the earliest page created.
   *
   * @return false|mixed
   */
  public function first() {
    $result = $this->select('pid', [$this->is_published], [
        'order' => 'created_date asc',
        'limit' => 1
      ]
    );

    return $result[0]['pid'] ?? null;
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
            
        WHERE " . $this->is_published

          . " AND t.tag = :tag
        
        ORDER BY p.created_date ASC LIMIT 1 
    ", [':tag' => $tag]);

    return $result[0]['picture_id'] ?? null;
  }

  /**
   * Find the last page created.
   *
   * @return false|mixed
   */
  public function last() {
    $result = $this->select('pid', [$this->is_published], [
        'order' => 'created_date desc',
        'limit' => 1
      ]
    );

    return $result[0]['pid'] ?? null;
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

        WHERE " . $this->is_published .

            " AND t.tag = :tag

        ORDER BY p.created_date DESC LIMIT 1 
    ", [':tag' => $tag]);

    return $result[0]['picture_id'] ?? null;
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
        AND " . $this->is_published

      . " ORDER BY created_date ASC
      
      LIMIT 1", [':created_date' => $created_date]
    );

    return $result[0]['pid'] ?? null;
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
            AND " . $this->is_published . " 
            AND tag = :tag
        
        ORDER BY created_date ASC
      
        LIMIT 1", [':created_date' => $created_date, ':tag' => $tag]
    );

    return $result[0]['pid'] ?? null;
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
        AND " . $this->is_published . " 
      
      ORDER BY created_date DESC
      
      LIMIT 1", [':created_date' => $created_date]
    );

    return $result[0]['pid'] ?? null;
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
            AND " . $this->is_published . " 
            AND tag = :tag
        
        ORDER BY created_date DESC
      
        LIMIT 1", [':created_date' => $created_date, ':tag' => $tag]
    );

    return $result[0]['pid'] ?? null;
  }

  /**
   * Gets a random page.
   *
   * @return mixed|null
   */
  public function randomPage() {
    $result = $this->db
      ->exec("SELECT id

              FROM pictures
              
              WHERE ". $this->is_published . " 
              
              ORDER BY rand()
              
              LIMIT 1");

    return $result[0]['id'] ?? null;
  }

  /**
   * Returns total pages.
   *
   * Will return only published for anonymous users.
   *
   * @return int
   */
  public function totalPages() {
    return $this->count([$this->is_published]);
  }

}