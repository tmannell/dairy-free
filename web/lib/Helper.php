<?php

/**
 * Class Helper
 */
Class Helper extends Main
{

  /**
   * Returns a component of the current path
   * OR
   * Returns all components of the current path as an array.
   *
   * @param null|int $key
   *  Key of path component. ie /story/13 = array(1 => story, 2 => 13)
   *
   * @return array|string
   */
  static function explodePath($key = null)
  {
    $f3 = \Base::instance();
    $path = strtolower(($f3->get('PATH')));
    // If the key is null return all path components
    if (is_null($key)) {
      $args = explode('/', $path);
    } // If key isn't null return the element of the path the key represents.
    else {
      $args = explode('/', $path)[$key];
    }

    // Remove empty values
    $args = array_filter($args, function ($value) {
      return $value !== '';
    });

    // Return cleaned array.
    return $args;
  }

  /**
   * Throw a 404 when called and arg is true.
   *
   * @param $bool
   * @return void
   */
  static function throw404($bool)
  {
    if ($bool === true) {
      Base::instance()->error(404);
    }
  }

}