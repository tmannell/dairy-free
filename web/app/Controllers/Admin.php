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
   * Content filter.
   * @var
   */
  protected $filter;

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
      $this->position - 1,
      $limit,
      $this->filter,
      [
        'order' => 'created_date desc'
      ]
    );


    if ($page['total'] === 0) {
      $template = new Template();
      echo $template->render('app/Views/noContent.htm');
      return false;
    }

    // Store the page count.
    $page_count = $page['count'];

    // The page count should not be less than the position.
    // Impossible!
    if ($page_count < $this->position) {
      Helper::throw404(true);
    }

    // Build the rows for our content table.
    $rows = [];
    foreach($page['subset'] as $res) {
      $rows[$res->get('pid')] = [
        'title' => trim($res->get('title')) === '' ? '...' : $res->get('title'),
        'publish_date' => date('Y-m-d', strtotime($res->get('publish_date'))),
        'created_date' => date('Y-m-d H:i', strtotime($res->get('created_date'))),
        'user_id' => $res->get('user_id'),
        'username' => $res->get('username'),
        'is_published' => $res->get('is_published') ? 'Published' : 'Unpublished',
      ];
    }

    // If the filter isn't null, we are on the user's content page
    // Lets load the user
    if ($this->filter !== NULL) {
      $user = new Users();
      $user->load(['id = ?', $this->filter[1]]);
      Helper::throw404($user->dry());
      $this->f3->set('viewed_username', $user->get('username'));
    }

    // Push the data to the template.
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
      $this->position - 1,
      $limit,
      null,
      [
        'order' => 'tag asc',
      ]
    );

    if ($page['total'] === 0) {
      $template = new Template();
      echo $template->render('app/Views/noContent.htm');
      return false;
    }

    // Store page count.
    $page_count = $page['count'];

    // The page count should not be less than the position.
    // Impossible!
    if ($page_count < $this->position) {
      Helper::throw404(true);
    }

    // Let's count how many times each
    // tag is used.
    $count = [];
    foreach ($page['subset'] as $tag) {
      $count[$tag->get('tag')]++;
    }

    // Build the rows.
    $rows = [];
    foreach($page['subset'] as $res) {
      $tag = $res->get('tag');
      $rows[$tag] = [
        'tag' => $tag,
        'uses' => $count[$tag],
      ];
    }

    // Push the data to the template.
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
      $this->position - 1,
      $limit,
      null,
      [
        'order' => 'id asc',
      ]
    );

    // Store page count.
    $page_count = $page['count'];

    if ($page['total'] === 0) {
      $template = new Template();
      echo $template->render('app/Views/noContent.htm');
      return false;
    }

    // The page count should not be less than the position.
    // Impossible!
    if ($page_count < $this->position) {
      Helper::throw404(true);
    }

    // The page count should not be less than the position.
    // Impossible!
    if ($page_count < $this->position) {
      Helper::throw404(true);
    }

    // Build the rows.
    $rows = [];
    foreach($page['subset'] as $res) {
      $id = $res->get('id');
      $rows[$id] = [
        'username' => $res->get('username'),
      ];
    }

    // Push the data to the template.
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
      $this->f3->set('entity', 'content');
      $this->f3->set('size', '10');
      $this->f3->set('prefix', '/admin');
      $this->position = isset($args[3]) ? $args[3] : 1;
      $this->filter = null;
      return true;
    }

    // User pages
    if ($args[1] === 'user' && is_numeric($args[2]) && $args[3] === 'content'
        && (!isset($args[4]) || is_numeric($args[4])))
    {
      $this->f3->set('entity', 'content');
      $this->f3->set('size', '10');
      $this->f3->set('prefix', '/user/' . $args['2']);
      $this->position = isset($args[4]) ? $args[4] : 1;
      $this->filter = ['user_id = ?', $args[2]];
      return true;
    }

    // tags
    if ($args[1] === 'admin' && $args[2] === 'tags') {
      $this->f3->set('entity', 'tags');
      $this->f3->set('size', '3');
      $this->f3->set('prefix', '/admin');
      $this->position = isset($args[3]) ? $args[3] : 1;
      return true;
    }

    // Users
    if ($args[1] === 'admin' && $args[2] === 'users') {
      $this->f3->set('entity', 'users');
      $this->f3->set('size', '4');
      $this->f3->set('prefix', '/admin');
      $this->position = isset($args[3]) ? $args[3] : 1;
      return true;
    }

    Helper::throw404(true);
  }

}