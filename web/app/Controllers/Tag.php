<?php

class Tag extends Main {

  protected $tags;

  public function __construct() {
    parent::__construct();

    $args = Helper::explodePath();

    if (isset($arg[3]) && is_string($args[3]) && !isset($args[4])) {
      $tags = new Tags();

      $this->tags = $tags->load(['tag = ?', $args[4]], ['order' => 'picture_id desc']);
    }
  }

}