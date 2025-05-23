<?php

/**
 * Class Tag
 */
class Tag extends Main {

  /**
   * The picture entity id.
   * @var
   */
  protected $picture_id;

  /**
   * The page id.
   * @var
   */
  protected $page;

  /**
   * A group of tag entities.
   * @var
   */
  protected $tags;

  /**
   * The tag or tag_name
   * @var
   */
  protected $tag_name;

  /**
   * Tag constructor.
   */
  public function __construct() {
    parent::__construct();
    $this->pathHandler();
  }

  /**
   * Displays a group of images categorized by tags.
   *
   * @throws \Exception
   */
  public function groupView() {
    $pages = new Pages();
    // Set template variables.
    $this->f3->set('path', '/group/' . $this->tag_name . '/');
    $this->f3->set('view_page', $this->picture_id);
    $this->f3->set('pid', $this->picture_id);
    $this->f3->set('first', $pages->firstInGroup($this->tag_name));
    $this->f3->set('previous', $pages->previousInGroup($this->tag_name, $this->page->get('created_date')));
    $this->f3->set('next', $pages->forwardInGroup($this->tag_name, $this->page->get('created_date')));
    $this->f3->set('last', $pages->lastInGroup($this->tag_name));
    $this->f3->set('media_type', $this->page->get('media_type'));
    $this->f3->set('filename', $this->page->get('filename'));
    $this->f3->set('title', $this->page->get('title'));
    $this->f3->set('media', $this->page->get('media'));

    // Format the date for the template.
    $date = new DateTime($this->page->get('created_date'));
    $this->f3->set('page_date', $date->format('Y-m-d'));

    // Render the template.
    $template = new Template;
    echo $template->render( 'app/Views/page.htm' );
  }

  /**
   * Edit tag page.
   */
  public function edit() {
    // Build form.
    $form = new Formr\Formr('bootstrap');
    // Set the form action.
    $form->action = '/tag/' . $this->tag_name . '/edit';
    // Set required fields
    $form->required = 'tag_name';
    $form->required_indicator = ' * ';

    // Set template variables.
    $this->f3->set('form', $form);
    $this->f3->set('tag_name', $this->tag_name);

    // If the form has been submitted.
    if ($form->submitted()) {
      // Get the submitted data.
      $data = $form->validate('tag_name');

      // Validate the data.
      if (trim($data['tag_name']) === '') {
        $form->add_to_errors('tag_name');
      }
      // Display error message.
      if ($form->errors() && $form->in_errors('tag_name')) {
        $form->error_message('Tag name is required.');
      }
      else {
        // Save the data, if there's no errors.
        while (!$this->tags->dry()) {
          // Format the tag.
          $tag = str_replace([' ', ','], '', strtolower($data['tag_name']));

          // Set and save.
          $this->tags->set('tag', $tag);
          $this->tags->save();
          $this->tags->next();
        }
      }

      // Set display message
      $this->f3->set('SESSION.message.type', 'alert-success');
      $this->f3->set('SESSION.message.text', 'Tag updated successfully.');
      // Redirect to admin page with query string.
      $this->f3->reroute("/admin/tags?editTag=1");
    }

    // Print the template.
    $template = new Template;
    echo $template->render( 'app/Views/editTag.htm' );
  }

  /**
   * Delete tag page.
   */
  public function delete() {
    // Build form.
    $form = new Formr\Formr('bootstrap');
    // Set the form action.
    $form->action = '/tag/' . $this->tag_name . '/delete';
    // Set template variables.
    $this->f3->set('form', $form);
    $this->f3->set('tag_name', $this->tag_name);

    // If the form has been submitted.
    if ($form->submitted()) {

      // Loop through all instances of the tag
      // and delete it, totally eradicating it from
      // the database.
      while(!$this->tags->dry()) {
        // Erase the page.
        $this->tags->erase();
        $this->tags->next();
      }

      // Set display message
      $this->f3->set('SESSION.message.type', 'alert-warning');
      $this->f3->set('SESSION.message.text', 'Tag deleted successfully.');
      // Redirect to admin page.
      $this->f3->reroute("/admin/tags");
    }

    // Print the template.
    $template = new Template;
    echo $template->render( 'app/Views/deleteTag.htm' );
  }

  /**
   * The tag path handler, decides what to do based on url
   * arguments.
   */
  protected function pathHandler() {
    // Get path args.
    $args = Helper::explodePath();

    // If we are on a group path and the tag is present.
    if (isset($args[2]) && strtolower($args[1]) === 'group') {
      $tags = new Tags();

      // If a page id is not present on the group path,
      // load the first page in the group.
      if (!isset($args[3])) {
        // Load the first tag in the group.
        $tags->load(['tag = ?', $args[2]], ['order' => 'picture_id asc', 'limit' => 1]);
        // Load page mapper.
        $this->page = new Pages();
        // Find the page id of the first page in the group.
        $pid = $this->page->firstInGroup($args[2]);
        // Throw a 404 if a pid is not found.
        Helper::throw404($pid === null);

        // Load the full page obj.
        $this->page->load(['pid = ?', $pid]);
        // Throw a 404 is the page isn't loaded.
        Helper::throw404($this->page->dry());

        // Store the tag name and picture id in class vars.
        $this->picture_id = $tags->get('picture_id');
        $this->tag_name = $tags->get('tag');

        // Check if the page is unpublished and set a var.
        if ($this->page->get('is_published') == 0) {
          $this->f3->set('unpublished', 1);
        }
      }
      // If a page id is present, lets load that specific page.
      elseif (is_numeric($args[3])) {
        // Load the tag assigned to the picture id.
        $tags->load(['tag = ? and picture_id = ?', $args[2], $args[3]], ['limit' => 1]);
        // Throw a 404 if no tag is found.
        Helper::throw404($tags->dry());

        // Load the page mapper.
        $this->page = new Pages();
        // Load the page found in the tags query.
        $this->page->load(['pid = ?', $tags->get('picture_id')]);
        // Throw a 404 if no page is found.
        Helper::throw404($this->page->dry());

        // If the page is unpublished and the user is anonymous
        // go to the next published page.
        if ($this->page->get('is_published') == 0 && $this->getAuthorizationStatus() === 'anonymous') {
          $this->page->forwardInGroup($args[2], $this->page->get('created_date'));
        }

        // Check if the page is unpublished and set a var, for templates.
        if ($this->page->get('is_published') == 0) {
          $this->f3->set('unpublished', 1);
        }

        // Store the tag name and picture id in class vars.
        $this->picture_id = $tags->get('picture_id');
        $this->tag_name = $tags->get('tag');
      }
    }
    // If we are on a tag edit page, the second argument will be set and the
    // 3rd argument must be 'edit' or 'delete'.
    elseif (isset($args[2]) && $args[2] !== 'group' && (strtolower($args[3]) === 'edit' || strtolower($args[3]) === 'delete')) {
      $this->tags = new Tags();
      $this->tags->load(['tag = ?', $args[2]]);

      // Throw a 404 if no tag is found.
      Helper::throw404($this->tags->dry());

      // Store tag name to class var.
      $this->tag_name = $args[2];
    }
  }

}