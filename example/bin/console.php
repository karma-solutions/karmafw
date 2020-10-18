#!/usr/bin/php
<?php

// CONFIG
define('APP_DIR', realpath(__DIR__ . '/..'));
define('VENDOR_DIR', APP_DIR . '/vendor');


// AUTOLOAD
$loader = require VENDOR_DIR . '/autoload.php';
$loader->setPsr4('App\\', APP_DIR . '/src');


use \KarmaFW\Kernel;
use \KarmaFW\App\Middlewares as KarmaMiddlewares;


ini_set('display_errors', 1);


$app = new Kernel([
    new KarmaMiddlewares\ErrorHandler,
    //new KarmaMiddlewares\ResponseTime,
    new KarmaMiddlewares\SessionHandler,
    new KarmaMiddlewares\CommandRouter($argv),
]);

$app->run();

