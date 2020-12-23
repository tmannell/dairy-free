<?php

require ('../vendor/autoload.php');
$f3 = Base::instance();

/*include config file and routes file*/
$f3->config('../config.ini' );
$f3->config('routes.ini');

// Sets custom 404 page.
$f3->set('ONERROR',function(){
  echo Template::instance()->render('app/Views/noContent.htm');
});

$main = new Main();
$main->startSession();
$main->userAccess();

$f3->run();

