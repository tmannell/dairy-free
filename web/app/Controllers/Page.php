<?php

/**
 * Class Page
 */
class Page extends Main {

  /**
   * @var
   */
  protected $pid;

  /**
   * @var
   */
  protected $page;

  /**
   * @var
   */
  protected $pages;

  /**
   * Page constructor.
   */
  public function __construct() {
    parent::__construct();
    $this->pathHandler();
  }


  /**
   * If you are directed to the homepage, display a random page.
   */
  public function home() {
    $pages = new Pages();
    $pid = $pages->randomPage();
    // Throw an error if nothing is returned.
    Helper::throw404($pid === null);

    // Reroute to random page.
    $this->f3->reroute("/page/$pid");

	}

  /**
   * If you are directed to the 'newest' url, go to
   * most recently created image.
   */
  public function newest() {
    $this->f3->reroute('/page/' . $this->pages->last());
  }

  /**
   * The view page callback.
   *
   * @throws \Exception
   */
  public function view() {

    // Set template variables.
    $this->f3->set('path', '/page/');
    $this->f3->set('view_page', $this->pid);
    $this->f3->set('pid', $this->pid);
    $this->f3->set('first', $this->page->first());
    $this->f3->set('previous', $this->page->previous($this->page->get('created_date')));
    $this->f3->set('next', $this->page->forward($this->page->get('created_date')));
    $this->f3->set('last', $this->page->last());
    $this->f3->set('filename', $this->page->get('filename'));
    $this->f3->set('media_type', $this->page->get('media_type'));
    $this->f3->set('media', $this->page->get('media'));
    $this->f3->set('title', $this->page->get('title'));

    // Format the date for the template.
    $date = new DateTime($this->page->get('created_date'));
    $this->f3->set('page_date', $date->format('Y-m-d'));

    // Render the template.
		$template = new Template;
		echo $template->render( 'app/Views/page.htm' );
	}

  /**
   * The add page form.
   *
   * @throws \Exception
   */
  public function add() {
    // Build Form
    $form = new Formr\Formr('bootstrap');
    // Set form action.
    $form->action = '/page/add';
    // All fields are required.
    $form->required = 'page_title, page_image, publish_date';
    $form->required_indicator = ' * ';
    // Turn off Formr default upload behavior.
    $form->uploads = FALSE;
    // Declare media option types
    $media_options = [
      0 => '-- None --',
      'link' => 'Link',
      'audio' => 'Audio File',
    ];

    // Set template vars.
    $this->f3->set('form', $form);
    $this->f3->set('logged_user', $this->f3->get('SESSION.uid'));
    $this->f3->set('publish_date_default', date('Y-m-d'));
    $this->f3->set('media_options', $media_options);

    // If the form has been submitted, handle it.
    if ($form->submitted()) {
      // Get form values.
      $data = $form->validate('
        page_title, 
        page_image, 
        publish_date, 
        is_published,  
        media_type, 
        page_link, 
        page_media,
        tags
      ');

      // Store the successful file uploads to the files array.
      $files = [];
      foreach ($_FILES as $key => $file) {
        if ($file['error'] === 0) {
          $files[$key] = $file;
        }
      }

      // Validate the submission.
      $valid = $this->validateForm($form, $data, $files, 'create');
      if ($valid === true) {
        // Save the submission.
        $picture_id = $this->create($form, $data, $files);
        // Creation successfull, redirect.
        if ($picture_id) {
          // Set display message.
          $this->f3->set('SESSION.message.type', 'alert-success');
          $this->f3->set('SESSION.message.text', 'Page added successfully.');
          // Reroute to page view.
          $this->f3->reroute('/page/' . $picture_id);
        }

        // Failed to create display error message.
        $form->error_message('Failed create page.');
      }
    }

    // Print template.
    $template = new Template;
    echo $template->render( 'app/Views/addPage.htm' );
  }

  /**
   * The edit page form.
   *
   * @throws \Exception
   */
  public function edit() {
    // Build Form
    $form = new Formr\Formr('bootstrap');
    // Set form action.
    $form->action = '/page/' . $this->pid . '/edit';
    // All fields are required.
    $form->required = 'page_title, publish_date, created_date';
    $form->required_indicator = ' * ';
    // Turn off Formr default upload behavior.
    $form->uploads = FALSE;
    // Declare media option types.
    $media_options = [
      0 => '-- None --',
      'link' => 'Link',
      'audio' => 'Audio File',
    ];

    // Get defaults for the page being edited.
    $defaults = $this->getDefaultValues();

    // Set template vars.
    $this->f3->set('form', $form);
    $this->f3->set('pid', $this->pid);
    $this->f3->set('media_options', $media_options);
    $this->f3->set('logged_user', $this->f3->get('SESSION.uid'));
    $this->f3->set('defaults', $defaults);

    // If the form has been submitted process it.
    if ($form->submitted()) {
      // Get form submission data.
      $data = $form->validate('
        page_title, 
        page_image, 
        publish_date,
        created_date,
        is_published,  
        media_type, 
        page_link, 
        page_media,
        tags
      ');

      // Store the successful file uploads to the files array.
      $files = [];
      foreach ($_FILES as $key => $file) {
        if ($file['error'] === 0) {
          $files[$key] = $file;
        }
      }

      // Validate the submission.
      $valid = $this->validateForm($form, $data, $files, 'update');
      if ($valid === true) {
        // Update the page.
        $picture_id = $this->update($form, $data, $files);
        // Update successful, redirect.
        if ($picture_id) {
          // Set display message.
          $this->f3->set('SESSION.message.type', 'alert-success');
          $this->f3->set('SESSION.message.text', 'Page updated successfully.');
          // Reroute to page view.
          $this->f3->reroute('/page/' . $picture_id);
        }

        // Not successful display error message
        $form->error_message('Failed to update page.');
      }
    }

    // Print template.
    $template = new Template;
    echo $template->render( 'app/Views/editPage.htm' );
  }

  /**
   * The page deletion form.
   */
  public function delete() {
    // Build form.
    $form = new Formr\Formr('bootstrap');
    // Set the form action.
    $form->action = '/page/' . $this->pid . '/delete';
    // Set template variables.
    $this->f3->set('form', $form);
    $this->f3->set('page_id', $this->pid);
    $this->f3->set('page_title', $this->page->get('title'));

    // If the form has been submitted.
    if ($form->submitted()) {
        $picture = new Pictures();
        $picture->load(['id = ?', $this->pid]);
        // Erase the page.
        $picture->erase();

        // Set display message.
        $this->f3->set('SESSION.message.type', 'alert-warning');
        $this->f3->set('SESSION.message.text', 'Page deleted successfully.');
        // Redirect to admin page.
        $this->f3->reroute("/admin/content");
    }

    // Print the template.
    $template = new Template;
    echo $template->render( 'app/Views/deletePage.htm' );
  }

  /**
   * Gather default values for edit form.
   *
   * @return array
   * @throws \Exception
   */
  protected function getDefaultValues() {
    $defaults = [];
    // Gather defaults.
    $defaults['title'] = $this->page->get('title');
    $defaults['publish_date'] = $this->page->get('publish_date');
    $defaults['media_type'] = $this->page->get('media_type');

    // Set created date default.
    $created_date = new DateTime($this->page->get('created_date'));
    $defaults['created_date'] = $created_date->format('Y-m-d') . 'T' . $created_date->format('H:i:s');

    // is_published default
    $defaults['is_published'] = '';
    if ($this->page->get('is_published') === 1) {
      $defaults['is_published'] = 'checked="checked"';
    }

    // Page link field.
    $defaults['page_link'] = '';
    if ($this->page->get('media_type') == 'link') {
      $defaults['page_link'] = $this->page->get('media');
    }

    // Get the tags
    $tags_ar = [];
    $tags = new Tags();
    $tags->load(['picture_id = :pid', ':pid' => $this->pid]);
    while(!$tags->dry()) {
      $tags_ar[] = $tags->tag;
      $tags->next();
    }

    // Format and save the tags.
    $tags_default = null;
    if (!empty($tags_ar)) {
      $defaults['tags'] = implode(', ', $tags_ar);
    }

    // Return the defaults.
    return $defaults;
  }

  /**
   * Validation for add and edit forms.
   *
   * @param $form
   * @param $data
   * @param $files
   * @param $op
   *
   * @return bool
   * @throws \Exception
   */
  protected function validateForm($form, $data, $files, $op) {

    // Validate file types.
    if ($op === 'create' || ($op === 'update' && isset($files['page_image']['name']))) {
      if (!in_array($files['page_image']['type'], [
        'image/jpeg',
        'image/png',
        'image/gif'
      ])) {
        $form->add_to_errors('page_image');
      }
    }

    // Only run this validation if the conditions are right.
    if ($op == 'create' || $op === 'update' && isset($files['page_media']['name'])) {
      // Media file type.
      if ($data['media_type'] === 'audio'
          && $files['page_media']['type'] !== 'audio/mpeg') {

        $form->add_to_errors('page_media');
      }

    }

    // Validate dates.
    // Create a date object using the entered date.
    $date_obj = new DateTime($data['publish_date']);
    if ($date_obj->format('Y-m-d') !== $data['publish_date']) {
      $form->add_to_errors('publish_date');
    }

    // Validate media types.
    if ($data['media_type'] === 'link') {
      if (trim($data['page_link']) === '') {
        $form->add_to_errors('link_empty');
      }
    }

    // Again only run this validation if the conditions are right.
    if ($op == 'create' || $op === 'update' && isset($files['page_media']['name'])) {

      // Has a file been uploaded?
      if ($data['media_type'] === 'audio' && trim($files['page_media']['name']) === '') {
          $form->add_to_errors('audio_empty');
      }
    }

    // Check for errors and print messages.
    if ($form->errors()) {
      if ($form->in_errors('page_title')) {
        $form->error_message('Title is required.');
      }
      if ($form->in_errors('page_image')) {
        $form->error_message('File missing or incorrect image file type. Allowed file types <em>jpg, png, gif</em>');
      }
      if ($form->in_errors('page_media')) {
        $form->error_message('Incorrect audio file type. Allowed file types <em>mp3</em>');
      }
      if ($form->in_errors('publish_date')) {
        $form->error_message('Publish date is in the incorrect format.');
      }
      if ($form->in_errors('created_date')) {
        $form->error_message('Date created is in the incorrect format.');
      }
      if ($form->in_errors('link_empty')) {
        $form->error_message('Missing media link.');
      }
      if ($form->in_errors('audio_image')) {
        $form->error_message('Missing audio file.');
      }

      return false;
    }

    return true;
  }

  /**
   * Creates a new page with add form submission.
   *
   * @param $form
   * @param $data
   * @param $files
   *
   * @return false
   */
  protected function create($form, $data, $files) {
    // Save the files before writing to the database.

    $img_filename = $this->saveFile($files['page_image'], 'pictures');
    if (!$img_filename) {
      $form->error_message('Failed to upload image file.');
      return false;
    }

    // Save audio file if necessary
    if ($data['media_type'] === 'audio' && isset($files['page_media']['name'])) {
      $audio_filename = $this->saveFile($files['page_media'], 'audio');
      if (!$audio_filename) {
        $form->error_message('Failed to upload audio file.');
        return false;
      }
    }

    // Now lets save the entities to the database.
    // First the picture.

    // Process the checkbox values.
    $is_published = $data['is_published'] === 'on' ? 1 : 0;

    $picture = new Pictures();
    $picture->set('title', $data['page_title']);
    $picture->set('filename', $img_filename);
    $picture->set('publish_date', $data['publish_date']);
    $picture->set('is_published', $is_published);
    $picture->set('user_id', $this->f3->get('SESSION.uid'));
    $picture = $picture->save();

    // Now let's save the media entity.
    $media_content = $data['media_type'] === 'link' ? $data['page_link'] : $audio_filename;

    $media = new Media();
    $media->set('media_type', $data['media_type']);
    $media->set('media', $media_content);
    $media->set('picture_id', $picture->id);
    $media->save();

    // Save the tags to the database.
    if (trim($data['tags']) !== '') {
      $tags = str_replace(' ', '', strtolower($data['tags']));
      $tags = explode(',', $tags);
      $tags = array_unique($tags);

      // Save the tags.
      foreach ($tags as $new_tag) {
        $tag = new Tags();
        $tag->set('tag', $new_tag);
        $tag->set('picture_id', $picture->id);
        $tag->save();
      }
    }

    // Return picture id if successful.
    // Used in redirect.
    return $picture->id;
  }

  /**
   * Updates a page with edit form submission.
   *
   * @param $form
   * @param $data
   * @param $files
   *
   * @return false
   * @throws \Exception
   */
  protected function update($form, $data, $files) {
    // Save the files before writing to the database.

    // Save image file if necessary.
    if (isset($files['page_image']['name'])) {
      $img_filename = $this->saveFile($files['page_image'], 'pictures');
      if (!$img_filename) {
        $form->error_message('Failed to upload image file.');
        return false;
      }
    }

    // Save audio file if necessary
    if ($data['media_type'] === 'audio' && isset($files['page_media']['name'])) {
      $audio_filename = $this->saveFile($files['page_media'], 'audio');
      if (!$audio_filename) {
        $form->error_message('Failed to upload audio file.');
        return false;
      }
    }

    // Now lets update the entities to the database.
    // First the picture.

    // Load the picture that's being edited.
    $picture_mapper = new Pictures();
    $picture = $picture_mapper->load(['id = ?', $this->pid]);

    // If it can't find the picture return false.
    if (!$picture) {
      return false;
    }

    $picture->set('title', $data['page_title']);
    $picture->set('publish_date', $data['publish_date']);
    $picture->set('user_id', $this->f3->get('SESSION.uid'));

    // Handle the image
    if (isset($img_filename)) {
      $picture->set('filename', $img_filename);
    }

    // Process the checkbox values.
    $is_published = $data['is_published'] === 'on' ? 1 : 0;
    $picture->set('is_published', $is_published);

    // Process created date.
    $date_obj = new DateTime($data['created_date']);
    $created_date = $date_obj->format('Y-m-d H:i:s');
    $picture->set('created_date', $created_date);
    $picture = $picture->save();

    $media_mapper = new Media();
    $media = $media_mapper->load(['picture_id = ?', $this->pid]);

    // If we can't find the media return false;
    if (!$media) {
      return false;
    }

    $media->set('media_type', $data['media_type']);

    // Now let's save the media entity.
    if ($data['media_type'] === 'link') {
      $media->set('media', $data['page_link']);
    }
    elseif($data['media_type'] === 'audio' && isset($audio_filename)) {
      $media->set('media', $audio_filename);
    }

    $media->set('picture_id', $picture->id);
    $media->save();

    // Save the tags to the database.
    if (trim($data['tags']) !== '') {
      $tags = str_replace(' ', '', strtolower($data['tags']));
      $tags = explode(',', $tags);
      $tags = array_unique($tags);

      // Remove any tags from the tags array that already exist
      // for this picture in the database.
      foreach ($tags as $key => $new_tag) {
        $tag_mapper = new Tags();
        $result = $tag_mapper->load(['tag = ? and picture_id = ?', $new_tag, $picture->id], ['limit' => 1]);
        if ($result) {
          unset($tags[$key]);
        }
      }

      // Save the tags.
      foreach ($tags as $new_tag) {
        $tag = new Tags();
        $tag->set('tag', $new_tag);
        $tag->set('picture_id', $picture->id);
        $tag->save();
      }
    }

    // Returns picture id on successful update.
    // Used in redirect.
    return $picture->id;
  }

  /**
   * Save a file upload!
   *
   * @param        $file
   * @param string $dir
   *
   * @return false|string
   */
  protected function saveFile($file, $dir = 'pictures') {
    // If a new file has been uploaded, modify file name to make it as unique
    // as possible.
    $img_filename = strtolower($file['name']);
    $img_filename = str_replace(' ', '_', $img_filename);
    $hash = hash('crc32', rand(0, 10000000));
    $img_filename = $hash . '__' . $img_filename;

    // Save the image file.
    if (!move_uploaded_file($file['tmp_name'], "assets/$dir/" . $img_filename)) {
      return false;
    }
    return $img_filename;
  }

  /**
   * The page path handler, decides what to do based on url
   * arguments.
   */
  protected function pathHandler() {
    // Get path arguments.
    $args = Helper::explodePath();
    // If argument two is numeric and argument one is 'page,
    // we are doing something with a page.
    if (is_numeric($args[2]) && strtolower($args[1]) === 'page') {
      // Store page id from URL.
      $this->pid = $args[2];
      // Load the pages mapper.
      $this->page = new Pages();

      // Editing or deleting a page.
      if (isset($args[3]) && (strtolower($args[3]) === 'edit' || strtolower($args[3]) === 'delete')) {
        // Load up the current page, no extra filters.
        $this->page->load(['pid = :pid', ':pid' => $this->pid]);
        // Throw a 404 if we can't load the page id.
        Helper::throw404($this->page->dry());
      }
      // Viewing a page.
      else {
        // The queries for admins vs anonymous are different.
        if (in_array($this->getAuthorizationStatus(), ['admin', 'authorized'], true)) {
          // Lets load up all pages including unpublished ones.
          $query = [
            'pid = :pid',
            ':pid' => $this->pid,
          ];
        }
        else {
          // Load only published pages for anonymous.
          $query = [
            'pid = :pid AND is_published = :is_published',
            ':pid' => $this->pid,
            ':is_published' => 1,
          ];
        }
        $this->page->load($query);
        // Throw a 404 if we can't find the page requested.
        Helper::throw404($this->page->dry());

        // Set a the unpublished var for templates.
        if ($this->page->get('is_published') === 0) {
          $this->f3->set('unpublished', 1);
        }
      }
    }
  }

}