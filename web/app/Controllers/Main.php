<?php

/**
 * Class Controller
 */
class Main {

  /**
   * @var static
   *  Base instance of fat-free.
   */
  protected $f3;
  /**
   * @var \DB\SQL
   *  DB instance
   */
  protected $db;

  /**
   * Current user id.
   * @var
   */
  protected $uid;

  /**
   * Controller constructor.
   *
   * Creates fat-free base instance and initial db connection.
   *
   * Main class is our base class, all other classes inherit.
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
   * Retrieves Authorization status.
   *
   * @return string
   */
  public function getAuthorizationStatus() {
    $uid = $this->f3->get('SESSION.uid');
    if ($uid == 1) {
      return 'admin';
    }

    return isset($uid) ? 'authorized' : 'anonymous';
  }

  /**
   * Starts user session.
   */
  public function startSession() {
    // Before we start session just make sure the DB exists.
    $result = $this->db->exec("SHOW TABLES LIKE 'users'");

    // If it does create the session
    if (!empty($result)) {
      new \DB\SQL\Session($this->db, 'sessions', TRUE);
    }
    // Otherwise go to the install path.
    elseif (empty($result) && $this->f3->get('PATH') !== '/install') {
      $this->f3->reroute('/install');
    }
  }

  /**
   * Check user access to all routes as defined in .env.
   */
  public function userAccess() {

    $access = Access::instance();
    $uid = $this->f3->get('SESSION.uid');
    if ($uid == 1) {
      $user_status = 'admin';
    }
    else {
      $user_status = isset($uid) ? 'authorized' : 'anonymous';
    }
    $access->authorize($user_status);
  }
}