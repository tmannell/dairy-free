<?php

/**
 * Class Helper
 */
Class Helper extends Main {

  /**
   * Returns a component of the current AngryGiant path
   * OR
   * Returns all components of the current AngryGiant path as an array.
   *
   * @param null $key
   *  Key of path component. ie www.angrygiant.com/story/13 = array(1 => story, 2 => 13)
   *
   * @return array|string
   */
  static function explodePath($key = null) {
    $f3 = \Base::instance();
    // If the key is null return all path components
    if (is_null($key)) {
      $args = explode('/', $f3->get('PATH'));
    }
    // If key isn't null return the element of the path the key represents.
    else {
      $args = explode('/', $f3->get('PATH'))[$key];
    }

    return $args;
  }

  static function throw404($bool) {
    if ($bool === true) {
      Base::instance()->error(404);
    }
  }

  /**
   * Takes a file uploaded via a form, resizes it and saves it to appropriate directories.
   *
   * @param $file
   *  File info sent from form.
   * @return string
   */
  static function resizeAndSaveImage($file) {
    $f3 = \Base::instance();

    while (true) {
      // Currently jpg is the only image format supported.
      $patterns = array('/\.[^.jpg]/i', '/\s/i', '/\.[^.jpeg]/i');
      // Replace all dots and spaces with underscores or nothing unless is the file extension.
      $replacements = array('', '_', '');
      // Tack a unique id onto the beginning of the filename so we don't end up overwriting
      // any existing files.
      $filename = preg_replace($patterns, $replacements, uniqid('', true) . $file['name']);

      // Double check if the file exists.  If it does, try again.
      if (!file_exists($f3->get('webroot') . 'pictures/original/' . $filename)) {
        break;
      }
    }

    // Resize and saved images in approprate directories. Using the
    // eventviva/php-image-resize library.
    $picture = new \Eventviva\ImageResize($file['path']);
    $picture->save($f3->get('webroot') . 'pictures/original/' . $filename);
    $picture->resizeToHeight($f3->get('imgLarge'));
    $picture->save($f3->get('webroot') . 'pictures/large/' . $filename);
    $picture->resizeToHeight($f3->get('imgMedium'));
    $picture->save($f3->get('webroot') . 'pictures/medium/' . $filename);
    $picture->resizeToHeight($f3->get('imgThumbnail'));
    $picture->save($f3->get('webroot') . 'pictures/thumbnail/' . $filename);

    // Return the filename for further use.
    return $filename;
  }

  static function checkErrors($renderer) {
    $errors = [];
    if (!empty($renderer->toArray()['errors'])) {
      foreach ($renderer->toArray()['errors'] as $field => $error) {
        Helper::setMessage($error, 'error');
        $errors[$field] = 'has-danger';
      }
    }
    return $errors;
  }

  static function modifyRenderedOutput($rendered) {
    $elements = [];
    foreach ($rendered as $element) {

      if (isset($element['type']) && $element['type'] == 'radio') {
        $elements[$element['name']][] = $element;
      }
      else {
        if (isset($element['name'])) {
          $elements[ $element['name'] ] = $element;
        }
      }
    }

    return ['attributes' => $rendered['attributes'], 'elements' => $elements];
  }
}