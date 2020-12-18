<?php

/**
 * Class Admin
 */
class Admin extends Main {

  /**
   * Paginator positoin.
   *
   * @var int|mixed
   */
  protected $position;

  /**
   * Admin constructor.
   */
  public function __construct() {
    parent::__construct();
    $this->pathHandler();
  }

  /**
   * Displays a list of page content with info and actions.
   */
  public function content() {
    // Define limit per page.
    $limit = 50;

    // Load up the page mapper.
    $page_mapper = new Pages();
    // Use fat free paginate function to get the numbers!
    $page = $page_mapper->paginate(
      $this->position,
      $limit,
      NULL,
      [
        'order' => 'created_date desc'
      ]
    );

    // Store the page count.
    $page_count = $page['count'];

    // Build the rows for our content table.
    $rows = [];
    foreach($page['subset'] as $res) {
      $rows[$res->get('pid')] = [
        'title' => $res->get('title'),
        'publish_date' => date('Y-m-d', strtotime($res->get('publish_date'))),
        'created_date' => date('Y-m-d H:i', strtotime($res->get('created_date'))),
        'user_id' => $res->get('user_id'),
        'username' => $res->get('username'),
        'is_published' => $res->get('is_published') ? 'Published' : 'Unpublished',
      ];
    }

    // Push the data to the template.
    $this->f3->set('header', 'app/Views/header.htm');
    $this->f3->set('footer', 'app/Views/footer.htm');
    $this->f3->set('pages', $rows);
    $this->f3->set('page_count', $page_count);
    $this->f3->set('position', $this->position);

    // Print template.
    $template = new Template;
    echo $template->render( 'app/Views/content.htm' );
  }

  /**
   * Builds a list of tags with actions.
   */
  public function tags() {
    // Define limit
    $limit = 50;
    // Load the tag mapper.
    $tags = new Tags();
    // Get the paginate numbers!
    $page = $tags->paginate(
      $this->position,
      $limit,
      null,
      [
        'order' => 'tag asc',
        'group' => 'tag'
      ]
    );

    // Store page count.
    $page_count = $page['count'];

    // Let's count how many times each
    // tag is used.
    $count = [];
    foreach ($page['subset'] as $tag) {
      $count[$tag->get('id')]++;
    }

    // Build the rows.
    $rows = [];
    foreach($page['subset'] as $res) {
      $id = $res->get('id');
      $rows[$id] = [
        'tag' => $res->get('tag'),
        'uses' => $count[$id],
      ];
    }

    // Push the data to the template.
    $this->f3->set('header', 'app/Views/header.htm');
    $this->f3->set('footer', 'app/Views/footer.htm');
    $this->f3->set('tags', $rows);
    $this->f3->set('page_count', $page_count);
    $this->f3->set('position', $this->position);

    // Print template.
    $template = new Template;
    echo $template->render( 'app/Views/tags.htm' );
  }

  /**
   * Builds a list of users with actions.
   */
  public function users() {
    // Define limit
    $limit = 50;
    // Load the tag mapper.
    $users = new Users();
    // Get the paginate numbers!
    $page = $users->paginate(
      $this->position,
      $limit,
      null,
      [
        'order' => 'id asc',
      ]
    );

    // Store page count.
    $page_count = $page['count'];

    // Build the rows.
    $rows = [];
    foreach($page['subset'] as $res) {
      $id = $res->get('id');
      $rows[$id] = [
        'username' => $res->get('username'),
      ];
    }

    // Push the data to the template.
    $this->f3->set('header', 'app/Views/header.htm');
    $this->f3->set('footer', 'app/Views/footer.htm');
    $this->f3->set('users', $rows);
    $this->f3->set('page_count', $page_count);
    $this->f3->set('position', $this->position);

    // Print template.
    $template = new Template;
    echo $template->render( 'app/Views/users.htm' );
  }

  /**
   * The admin path handler, decides what to do based on url
   * arguments.
   */
  protected function pathHandler() {
    $args = Helper::explodePath();
    // Pages
    if ($args[1] === 'admin' && $args[2] === 'content') {
      $this->position = isset($args[3]) ? $args[3] : 1;
    }

    // tags
    if ($args[1] === 'admin' && $args[2] === 'tags') {
      $this->position = isset($args[3]) ? $args[3] : 1;
    }

    // Users
    if ($args[1] === 'admin' && $args[2] === 'users') {
      $this->position = isset($args[3]) ? $args[3] : 1;
    }
  }

}