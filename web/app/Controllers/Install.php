<?php

/**
 * Class Install
 */
class Install extends Main {

  /**
   * Checks install.
   *
   * Before anything is done this function is called
   * to check if the database has been installed already.
   */
  public function installCheck() {
    // Check if user table exists.
    $result = $this->db->exec("SHOW TABLES LIKE 'users'");

    // If it doesn't, lets go to the install form.
    if (empty($result)) {
      $this->installForm();
    }
    // Otherwise print message.
    else {
      echo 'The site has already been installed.';
    }
  }

  /**
   * The installation form, get install data from
   * admin user.
   */
  public function installForm() {
    // Build the form.
    $form = new Formr\Formr('bootstrap');
    // All fields are required.
    $form->required = TRUE;
    // Set the form action.
    $form->action = '/install';
    $this->f3->set('header', 'app/Views/header.htm');
    $this->f3->set('form', $form);
    $this->f3->set('footer', 'app/Views/footer.htm');

    // If the form has been submitted.
    if ($form->submitted()) {
      // Get the submission data.
      $data = $form->validate('Username, Password(min[8]), Re-enter Password(matches[password]), Database Name(min[3])');

      // Validate the form.
      if ($form->errors()) {
        if ( $form->in_errors('re-enter_password')) {
          $form->error_message( 'Passwords do not match.' );
        }

        if ( $form->in_errors( 'password' ) ) {
          $form->error_message( 'Password must be a minimum of 8 characters' );
        }

        if ($form->in_errors('database_name')) {
          $form->error_message('Database name muse be a minimum of 3 characters');
        }
      }
      // If the form passes validation, create the database, add the admin user.
      else {
        $this->createDatabase($data['database_name']);
        $this->addAdminUser( $data['username'], $data['password'] );
        $this->f3->reroute( '/login' );
      }
    }

    // Print the template.
    $template = new Template;
    echo $template->render( 'app/Views/install.htm' );
  }

  /**
   * Creates the database.
   *
   * @param $db_name
   */
  protected function createDatabase($db_name) {

    // Create the database.
    $this->db->begin();
    $this->db->exec(
      "CREATE TABLE IF NOT EXISTS `$db_name`.`users` (
           `id` INT AUTO_INCREMENT PRIMARY KEY,
           `username` VARCHAR(45) NOT NULL UNIQUE,
           `password` VARCHAR(100) NOT NULL
       ) ENGINE = InnoDB;

       CREATE INDEX `user_id_idx` ON `$db_name`.`users` (`id` ASC);"
    );

    $this->db->exec(
      "CREATE TABLE IF NOT EXISTS `$db_name`.`pictures` (
           `id` INT AUTO_INCREMENT PRIMARY KEY,
           `title` VARCHAR(5000) NULL,
           `filename` VARCHAR(100) NOT NULL UNIQUE,
           `user_id` INT NOT NULL,
           `is_published` INT NOT NULL,
           `publish_date` DATE NOT NULL,
           `created_date` DATETIME DEFAULT CURRENT_TIMESTAMP UNIQUE,
    
         FOREIGN KEY (`user_id`)
         REFERENCES `$db_name`.`users` (`id`)
         ON DELETE NO ACTION
         ON UPDATE cascade
       ) ENGINE = InnoDB;

       CREATE INDEX `picture_id_idx` ON `$db_name`.`pictures` (`id` ASC);"
    );

    $this->db->exec(
      "CREATE TABLE IF NOT EXISTS `$db_name`.`media` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `media_type` VARCHAR(45) NOT NULL,
         `media` VARCHAR(1000) NOT NULL,
         `picture_id` INT NOT NULL,
      
         FOREIGN KEY (`picture_id`)
         REFERENCES `$db_name`.`pictures` (`id`)
         ON DELETE CASCADE
         ON UPDATE NO ACTION
       ) ENGINE = InnoDB;

       CREATE INDEX media_id_idx ON `$db_name`.`media` (`id` ASC);"
    );

    $this->db->exec(
      "CREATE TABLE IF NOT EXISTS `$db_name`.`tags` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `tag` VARCHAR(45) NOT NULL,
         `picture_id` INT NOT NULL,
      
         FOREIGN KEY (`picture_id`)
         REFERENCES `$db_name`.`pictures` (`id`)
         ON DELETE CASCADE
         ON UPDATE NO ACTION
       ) ENGINE = InnoDB;

       CREATE INDEX tag_id_idx ON `$db_name`.`tag` (`id` ASC);"
    );
    // Create the database.
    $this->db->commit();

    // Now that it's created, lets build a view, for a nice big model.
    $this->db->exec(
      "CREATE VIEW pages AS
        SELECT users.username,
               pictures.id as pid,
               pictures.filename,
               pictures.title,  
               pictures.is_published,
               pictures.publish_date,
               pictures.created_date,
               pictures.user_id, 
               media.id as mid, 
               media.media_type, 
               media.media          
        FROM users
        INNER JOIN pictures on users.id = pictures.user_id
        LEFT JOIN media on pictures.id = media.picture_id"
    );
  }

  /**
   * Adds the admin user.
   *
   * @param $username
   * @param $password
   */
  protected function addAdminUser($username, $password) {
    $user = new User();
    $this->db->exec(
      "INSERT INTO users (username, password) VALUES (?, ?)",
      [
        1 => $username,
        2 => $user->cryptPassword($password),
      ]
    );
  }

}