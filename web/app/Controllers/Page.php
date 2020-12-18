<?php

class Page extends Main {

  protected $pid;
  protected $page;
  protected $pages;

  public function __construct() {
    parent::__construct();

    // Get path arguments.
    $args = Helper::explodePath();
    // Means we are doing something with a page.
    if (is_numeric($args[2])) {
      // Store page id from URL.
      $this->pid = $args[2];
      // Load the pages mapper.
      $this->page = new Pages();
    }

    // Editing a page.
    if (is_numeric($args[2]) && isset($args[3])
        && (strtolower($args[3]) == 'edit' || strtolower($args[3] == 'delete')))
    {
      // Load up the current page, no extra filters.
      $this->page->load(['pid = :pid', ':pid' => $this->pid]);
    }
    // Viewing a page.
    elseif (is_numeric($args[2]) && !isset($args[3])) {
      // Load only published pages.
      $this->page->load(
        ['pid = :pid AND is_published = :is_published',
          ':pid' => $this->pid,
          ':is_published' => 1,
        ]);

      // if the obj wasn't populated lets redirect to a 404.
      if ($this->page->dry()) {
        $this->f3->error(404);
      }
    }
  }


  function home() {
    $result = $this->db
      ->exec('SELECT id
              FROM pictures
              ORDER BY rand()
              LIMIT 1');
    $page_id = $result[0]['id'];
    $this->f3->reroute("/page/$page_id");
	}

  function newest() {
    $this->f3->reroute('/' . $this->pages->last());
  }

  function view() {

    // Set template variables.
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
    // Add the footer and headers as vars.
    $this->f3->set('header', 'app/Views/header.htm');
    $this->f3->set('footer', 'app/Views/footer.htm');

    // Render the template.
		$template = new Template;
		echo $template->render( 'app/Views/page.htm' );
	}

  function add() {
    // Build Form
    $form = new Formr\Formr('bootstrap');
    // All fields are required.
    $form->required = 'page_title, page_image, publish_date';
    $form->required_indicator = ' * ';
    // Turn off Formr default upload behavior.
    $form->uploads = FALSE;
    // Declare the forms action.
    $form->action = '/user/add';
    // Declare media option types
    $media_options = [
      0 => '-- None --',
      'link' => 'Link',
      'audio' => 'Audio File',
    ];

    // Set template vars.
    $this->f3->set('header', 'app/Views/header.htm');
    $this->f3->set('form', $form);
    $this->f3->set('logged_user', $this->f3->get('SESSION.uid'));
    $this->f3->set('publish_date_default', date('Y-m-d'));
    $this->f3->set('media_options', $media_options);
    $this->f3->set('footer', 'app/Views/footer.htm');

    if ($form->submitted()) {
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
      $valid = $this->validateForm($form, $data, $files);
      if ($valid === true) {
        $this->saveSubmission($form, $data, $files);
      }
    }

    // Print template.
    $template = new Template;
    echo $template->render( 'app/Views/addPage.htm' );
  }

  function edit() {
    // Build Form
    $form = new Formr\Formr('bootstrap');
    // All fields are required.
    $form->required = 'page_title, page_image, publish_date';
    $form->required_indicator = ' * ';
    // Turn off Formr default upload behavior.
    $form->uploads = FALSE;
    // Declare the forms action.
    $form->action = '/user/add';
    // Declare media option types
    $media_options = [
      0 => '-- None --',
      'link' => 'Link',
      'audio' => 'Audio File',
    ];

    // Get defaults for the page being edited.
    $defaults = $this->getDefaultValues();

    // Set template vars.
    $this->f3->set('header', 'app/Views/header.htm');
    $this->f3->set('form', $form);
    $this->f3->set('pid', $this->pid);
    $this->f3->set('media_options', $media_options);
    $this->f3->set('logged_user', $this->f3->get('SESSION.uid'));
    $this->f3->set('defaults', $defaults);
    $this->f3->set('footer', 'app/Views/footer.htm');

    if ($form->submitted()) {
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
      $valid = $this->validateForm($form, $data, $files);
      if ($valid === true) {
        $this->saveSubmission($form, $data, $files);
      }
    }


    // Print template.
    $template = new Template;
    echo $template->render( 'app/Views/editPage.htm' );
  }

  function delete() {
    // Build form.
    $form = new Formr\Formr('bootstrap');
    // Set the form action.
    $form->action = '/page/' . $this->pid . '/delete';
    // Set template variables.
    $this->f3->set('header', 'app/Views/header.htm');
    $this->f3->set('page_id', $this->pid);
    $this->f3->set('page_title', $this->page->get('title'));
    $this->f3->set('footer', 'app/Views/footer.htm');

    // If the form has been submitted.
    if ($form->submitted()) {
        // Get the next page for redirection.
        $next = $this->page->next();
        // Erase the page.
        $this->page->erase();
        // Redirect to admin page with query string.
        $this->f3->reroute("/page/$next");
    }

    // Print the template.
    $template = new Template;
    echo $template->render( 'app/Views/deletePage.htm' );
  }

  protected function getDefaultValues() {
    $defaults = [];
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

  protected function validateForm($form, $data, $files) {

    // Validate file types.
    if (!in_array($files['page_image']['type'], [
      'image/jpeg',
      'image/png',
      'image/gif'
    ])) {
      $form->add_to_errors('page_image');
    }
    if ($data['media_type'] === 'audio'
        && $files['page_media']['type'] !== 'audio/mpeg') {

      $form->add_to_errors('page_media');
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
    if ($data['media_type'] === 'audio') {
      if (trim($files['page_media']['name']) === '') {
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

  protected function saveSubmission($form, $data, $files) {
    // Save the files before writing to the database.

    // IMAGE FILE
    // Modify file name to make it as unique as possible.
    $img_filename = strtolower($files['page_image']['name']);
    $img_filename = str_replace(' ', '_', $img_filename);
    $hash = hash('crc32', rand(0, 10000000));
    $img_filename = $hash . '__' . $img_filename;

    // Save the image file.
    if (!move_uploaded_file($files['page_image']['tmp_name'], '/app/web/assets/pictures/' . $img_filename)) {
      $form->error_message('Failed to upload image file.');
      return FALSE;
    }

    // If there's an audio file.
    if ($data['media_type'] === 'audio') {

      // Modify the filename so it's as unique as possible.
      $aud_filename = strtolower($files['page_media']['name']);
      $aud_filename = str_replace(' ', '_', $aud_filename);
      $hash     = hash('crc32', rand(0, 10000000));
      $audio_filename = $hash . '__' . $aud_filename;

      // Save the media file.
      if (!move_uploaded_file($files['page_media']['tmp_name'], '/app/web/assets/audio/' . $audio_filename)) {
        $form->error_message('Failed to upload audio file.');
        return FALSE;
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

    // Process created date.
    if (isset($data['created_date'])) {
      $date_obj = new DateTime($data['created_date']);
      $created_date = $date_obj->format('Y-m-d H:i:s');
      $picture->set('created_date', $created_date);
    }

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
      $tags = str_replace(' ', '', $data['tags']);
      $tags = explode(',', $tags);
      foreach ($tags as $new_tag) {
        $tag = new Tags();
        $tag->set('tag', strtolower($new_tag));
        $tag->set('picture_id', $picture->id);
        $tag->save();
      }
    }

    $form->success_message('Page added successfully.');
    $this->f3->reroute('/page/' . $picture->id);
  }
}