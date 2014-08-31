<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

$app = new Silex\Application();
require_once __DIR__.'/../src/app.php';
$app->run();
