<?php

/**
 * Class CronJobs
 */
class CronJobs {

  /**
   * @var \Base|object
   */
  protected $f3;

  /**
   * @var \DB\SQL
   */
  protected $db;

  /**
   * CronJobs constructor.
   */
  public function __construct() {
    // Set up f3 base.
    $this->f3 = Base::instance();

    // Initiate the database/
    $this->db = new \DB\SQL(
      $this->f3->get('database'),
      $this->f3->get('username'),
      $this->f3->get('password')
    );
  }

  /**
   * Auto publish function. Publishes
   * pages automatically based on publish date.
   */
  public function autoPublish() {
    // Get current date.
    $current_date = new DateTime();
    // Published all unpublished pages that have a
    // published date that is equal or less than today.
    $this->db->exec("
      UPDATE pictures 
      
      SET is_published = 1

      WHERE is_published = 0
        
        AND publish_date <= :current_date 
    ", [':current_date' => $current_date->format('Y-m-d')]);
  }

}