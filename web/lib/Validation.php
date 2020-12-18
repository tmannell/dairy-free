<?php

/**
 * Class Validation
 */
class Validation extends Main {

  /**
   * Validation constructor.
   */
  function __construct() {
    parent::__construct();
  }

  /**
   * Validates image dimensions defined in config.ini
   *
   * @param $file
   *  The file info submitted through a form.
   * @return bool
   */
  function validatePictureDimensions($file) {
    $image_info = getimagesize($file['tmp_name']);
    $image_width  = $image_info[0];
    $image_height = $image_info[1];
    if ($image_width >= $this->f3->get('imgLarge')
      && $image_height >= $this->f3->get('imgMinHeight')) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Validates image mime type
   *  currently angryGiant only supports jpeg.
   *
   * @param $file
   *  The file info submitted through a form.
   * @return bool
   */
  function validateMimeType($file) {
    if ($file['type'] == 'image/jpeg') {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Validation function
   *  Makes sure username does not already exist.
   *
   * @param $username
   *  The username to check.
   *
   * @return bool
   */
  function validate_username($username) {
    $user = new User();
    $user->load(['username = ?', $username]);

    if (isset($user->username) && $user->username) {
      return false;
    }
    else {
      return true;
    }
  }

  /**
   * Validation function
   *  Ensure that two fields that are supposed to match do.
   *
   * @param $originalFieldValue
   *  Value from first field
   * @param $compareField
   *  Value from the comparative field.
   * @return bool
   */
  function validate_match_field($originalFieldValue, $compareField) {
    if ($originalFieldValue == $compareField) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Validation function
   *  Makes sure super user is not being deleted.
   *
   * @param $uid
   *  User id of user being deleted
   * @return bool
   */
  function validate_user_deletion($uid) {
    if ($uid == 1) {
      return false;
    }
    else {
      return true;
    }
  }

  /**
   * Validation function
   *  Validates the password by comparing form submitted data with
   *  user info in the database.
   *
   * @param $password
   *  User submitted password.
   * @param $username
   *  Username form name, used to get actual submitted value.
   * @return bool
   */
  function validatePassword($password, $username) {
    $user = new User();
    // Pass username and password to our custom auth function.
    if ($user->authenticateUser($username, $password)) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   *  Validates page number is unique to it's parent (story).
   * @param $pageNumber
   *  The page number submitted via form.
   * @param $storyId
   *  The story id the page belongs to also submitted via form.
   * @return bool
   */
  function validatePageNumber($pageNumber, $storyId) {
    $page = new Page;
    $page->load(['story_id = ? and page_number = ?', $storyId, $pageNumber]);
    return ($page->id) ? false : true;
  }

  function validateShortTitle($shortTitle, $identifier) {
    if (!isset($identifier)) {
      $story = new Story;
      $story->load(['short_title = ?', $shortTitle]);
      return ($story->id) ? false : true;
    }

    return true;
  }
}