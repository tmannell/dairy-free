<?php

class Admin extends Main {

  protected $position;

  public function __construct() {
    parent::__construct();
    $args = Helper::explodePath();
    if ($args[1] === 'admin' && $args[2] === 'content') {
      $this->position = isset($args[3]) ? $args[3] : 1;
    }
  }

  public function content() {
    // Define limit
    $limit = 50;
    $pages = new Pages();
    $page = $pages->paginate(
      $this->position,
      $limit,
      NULL,
      [
        'order' => 'created_date desc'
      ]
    );

    $page_count = $page['count'];

    $rows = [];
    foreach($page['subset'] as $res) {
      $user = new Users();
      $user->load(['id = ?', $res->get('user_id')]);
      $rows[$res->get('pid')] = [
        'title' => $res->get('title'),
        'publish_date' => date('Y-m-d', strtotime($res->get('publish_date'))),
        'created_date' => date('Y-m-d H:i', strtotime($res->get('created_date'))),
        'user_id' => $res->get('user_id'),
        'username' => $res->get('username'),
        'is_published' => $res->get('is_published') ? 'Published' : 'Unpublished',
      ];
    }

    $this->f3->set('header', 'app/Views/header.htm');
    $this->f3->set('footer', 'app/Views/footer.htm');
    $this->f3->set('pages', $rows);
    $this->f3->set('page_count', $page_count);
    $this->f3->set('position', $this->position);

    // Print template.
    $template = new Template;
    echo $template->render( 'app/Views/content.htm' );
  }
}