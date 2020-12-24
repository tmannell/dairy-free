<?php

/**
 * Class UserController
 */
Class User extends Main {

  /**
   * @var \User
   *  User Obj.
   */
  private $user;

  /**
   * UserController constructor.
   *
   * Inherits constructor from Controller class.
   */
  public function __construct() {
    parent::__construct();
    $this->pathHandler();
  }

  /**
   * Admin login form.
   *
   * Upon successful login adds session var (user id). We are using this var
   * to authorize users.
   */
  public function login() {
    // Reroute user to view user page is they are already logged in.
    $authStatus = $this->getAuthorizationStatus();
    if ($authStatus == 'authorized' || $authStatus == 'admin' ) {
      $this->f3->reroute('/user/' . $this->f3->get('SESSION.uid'));
    }

    // Build Login form
    $form = new Formr\Formr('bootstrap');
    // All fields required.
    $form->required = TRUE;
    // Declare the forms action property.
    $form->action = '/login';
    // Set some vars for the template.
    $this->f3->set('form', $form);

    // If the form has been submitted.
    if ($form->submitted()) {
      // Pull the submission data.
      $data = $form->validate('username, password');

      // Load the Users model.
      $user = new Users();
      // Load the user that is attempting to login.
      $user->load(['username = ?', $data['username']]);
      // Validate the user.
      if ($user->dry() || $this->authenticateUser($data['username'], $data['password']) == FALSE) {
       $form->add_to_errors('username');
       $form->add_to_errors('password');
      }

      // Form the error messages if there are errors.
      if ($form->errors()) {
        if ($form->in_errors('username') && $form->in_errors('password')) {
          $form->error_message('Username or password incorrect.');
        }
      }
      // The form passes validation if we get to this else.
      else {
        // Set the uid in the session for auth purposes.
        $this->f3->set('SESSION.uid', $user->id);
        // Redirect user to their user page.
        $this->f3->reroute('/user/' . $user->id);
      }
    }

    // Print the template.
    $template = new Template();
    echo $template->render( 'app/Views/login.htm' );
  }

  /**
   * Displays user page.
   */
  public function view() {

    // Get user's total page count.
    $pages = new Pages();
    $total_posts = $pages->count(['user_id = ?', $this->uid]);

    $creation_date = 'Never!';
    if ($total_posts > 0) {
      // Latest creation date.
      $pages->select('created_date', [
        'limit' => 1,
        'order' => 'creation_date desc'
      ]);
      $date = $pages->get('created_date');
      $date_obj = new DateTime($date);
      $creation_date = $date_obj->format('Y-m-d H:i');
    }

    // Add vars to the template.
    $this->f3->set('username', $this->user->get('username'));
    $this->f3->set('logged_user', $this->f3->get('SESSION.uid'));
    $this->f3->set('viewed_user', $this->uid);
    $this->f3->set('total_posts', $total_posts);
    $this->f3->set('latest', $creation_date);

    // Print the template.
    $template = new Template();
    echo $template->render('app/Views/viewUser.htm');
  }

  /**
   * Create add user form.
   */
  public function add() {
    // Build the add form.
    $form = new Formr\Formr('bootstrap');
    // All fields are required.
    $form->required = TRUE;
    // Set form action
    $form->action = '/user/add';
    // Set template vars.
    $this->f3->set('form', $form);


    // If the form has been submitted.
    if ($form->submitted()) {
      // Get the submission data.
      $data = $form->validate('username, password(min[8]), password2(matches[password])');

      // Run validation.
      if ($form->errors()) {

        if ( $form->in_errors( 'password' ) ) {
          $form->error_message( 'Password must be a minimum of 8 characters' );
        }

        if ( $form->in_errors('password2')) {
          $form->error_message( 'Passwords do not match.' );
        }
      }
      // If validation passes, process the submission.
      else {
        // Create the new user.
        $user = new Users();
        $user->set('username', $data['username']);
        $user->set('password', $this->cryptPassword($data['password']));
        $user->insert();
        // Display the success message.
        $this->f3->set('SESSION.message.type', 'alert-success');
        $this->f3->set('SESSION.message.text', 'User ' . $data['username'] . ' (' . $user->id . ') has been created.');
        // Reroute to admin page.
        $this->f3->reroute('/admin/users');
      }
    }

    // Print the template.
    $template = new Template;
    echo $template->render( 'app/Views/addUser.htm' );
  }

  /**
   * Edit user form.
   */
  public function edit() {
    // Build form.
    $form = new Formr\Formr('bootstrap');
    // All form fields are required.
    $form->required = TRUE;
    // Set the form action.
    $form->action = '/user/' . $this->uid . '/edit';
    // Template vars.
    $this->f3->set('viewed_user_id', $this->user->get('id'));
    $this->f3->set('viewed_username', $this->user->get('username'));
    $this->f3->set('form', $form);

    // If the form has been submitted.
    if ($form->submitted()) {
      // Grab submission variables.
      $data = $form->validate('Username, Password(min[8]), Re-enter Password(matches[password])');

      // Run the validation.
      if ($form->errors()) {
        if ( $form->in_errors('password2')) {
          $form->error_message( 'Passwords do not match.' );
        }

        if ( $form->in_errors( 'password' ) ) {
          $form->error_message( 'Password must be a minimum of 8 characters' );
        }
      }
      // If the form passes validation, process the submission.
      else {
        $this->user->set('username', $data['username']);
        $this->user->set('password', $this->cryptPassword($data['password']));
        $this->user->update();

        $this->f3->set('SESSION.message.type', 'alert-success');
        $this->f3->set('SESSION.message.text', 'User ' . $this->user->get('username') . ' (' . $this->user->get('id') . ') has been updated.');
        $this->f3->reroute('/admin/users');
      }
    }

    // Print template.
    $template = new Template;
    echo $template->render( 'app/Views/editUser.htm' );
  }

  /**
   * Delete user form.
   *   Removes user from database.
   */
  function delete() {
    // Build form.
    $form = new Formr\Formr('bootstrap');
    // Set form action.
    $form->action = '/user/' . $this->user->get('id') . '/delete';

    // Set template variables.
    $this->f3->set('form', $form);
    $this->f3->set('viewed_user_id', $this->user->get('id'));
    $this->f3->set('viewed_username', $this->user->get('username'));

    // If the form has been submitted.
    if ($form->submitted()) {
      if ($this->user->get('id') == 1) {
        $form->error_message('Cannot delete the admin user.');
      }
      else {
        // Delete the user.
        $this->user->erase();

        $this->f3->set('SESSION.message.type', 'alert-success');
        $this->f3->set('SESSION.message.text', 'User ' . $this->user->get('username') . ' (' . $this->user->get('id') . ') has been deleted.');
        // redirect to admin page with query string.
        $this->f3->reroute('/admin/users');
      }
    }

    // Print the template.
    $template = new Template;
    echo $template->render( 'app/Views/deleteUser.htm' );
  }

  /**
   * Logs user out by clearing uid in session var.
   */
  public function logout() {
    // Clear the user out of the session.
    $this->f3->clear('SESSION.uid');
    $this->f3->reroute('/');
  }

  /**
   * Uses PHP crypt to encrypt password using BlowFish
   *
   * @param $input
   *  Input from password submission form.
   * @param int $cost
   *  Rounds of encryption
   * @return string
   *  returns the password hash for storage in database.
   */
  public function cryptPassword($input, $cost = 7) {
    $salt = "";
    $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
    for($i=0; $i < 22; $i++) {
      $salt .= $salt_chars[array_rand($salt_chars)];
    }
    return crypt($input, sprintf('$2a$%02d$', $cost) . $salt);
  }

  /**
   * Authenticates user passwords, compares them to the hash in the database.
   *
   * @param $username
   *  Username retrieved from form
   * @param $password
   *  Password retrieved from form.
   * @return bool
   *  returns password hash if correct otherwise returns false.
   *
   */
  public function authenticateUser($username, $password) {
    $db = $this->db;
    $result = $db->exec('SELECT * FROM users WHERE username = ?', [1 => $username]);
    if(crypt($password, $result[0]['password']) === $result[0]['password']) {
      return $result[0]['password'];
    }
    else {
      return false;
    }
  }

  /**
   * The user path handler, decides what to do based on url
   * arguments.
   */
  protected function pathHandler() {
    $args = Helper::explodePath();
    // If we are not adding a new user lets load up the
    // current user obj and store the uid in a separate var.
    if (is_numeric($args[2]) && strtolower($args[1]) === 'user') {

      // Get the user id from URL
      $this->uid = $args[2];

      // Load story obj based on identifier.
      $this->user = new Users();
      $this->user->load(['id = ?', $this->uid]);
      // Throw a 404 if we can't find a user.
      Helper::throw404($this->user->dry());
    }
  }

}