<?php

// CONFIG
define('APP_DIR', realpath(__DIR__ . '/..'));
define('VENDOR_DIR', APP_DIR . '/vendor');


// AUTOLOAD
$loader = require VENDOR_DIR . '/autoload.php';
$loader->setPsr4('App\\', APP_DIR . '/src');


use \KarmaFW\App;
use \KarmaFW\App\Request;
use \KarmaFW\App\Response;
use \KarmaFW\App\Middlewares as KarmaMiddlewares;


ini_set('display_errors', 1);


\KarmaFW\WebApp::boot();
\KarmaFW\WebApp::routeUrl();

