<?php

require ('../vendor/autoload.php');
// Instantiate fat-free base.
$f3 = Base::instance();

// Include config.ini for db creds.
$f3->config('../config.ini' );
// Include cron definitions.
$f3->config('../cron.ini');
// Instantiate the cron class.
Cron::instance();

$f3->run();