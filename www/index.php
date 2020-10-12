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


// Build request
$request = Request::createFromGlobals();

// Init App and Define workflow
$app = new App([
    new KarmaMiddlewares\TrafficLogger,
    new KarmaMiddlewares\ErrorHandler,
    new KarmaMiddlewares\ResponseTime,
    new KarmaMiddlewares\ForceHttps,
    //new KarmaMiddlewares\GzipEncoding,
    //new KarmaMiddlewares\MaintenanceMode,
    new KarmaMiddlewares\LoadHelpers,
    new KarmaMiddlewares\SessionHandler,
    //'handle404',
    //'Authentification',
    //'CacheHtml',
    //new KarmaMiddlewares\UrlPrefixRouter,
    //new KarmaMiddlewares\CommandRouter($argv),
    new KarmaMiddlewares\UrlRouter,
]);

// Process App workflow/pipe and return a $response
$response = $app->handle($request);

// Send $response->content to the client (browser or stdout)
$response->send();

