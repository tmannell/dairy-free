<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/unit/BaseTestCase.php';

$f3 = Base::instance();
$f3->config(__DIR__ . "/../config.test.ini");
$f3->config(__DIR__ . '/../web/routes.ini');

