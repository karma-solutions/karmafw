<?php

// CONFIG
define('APP_DIR', realpath(__DIR__ . '/..'));
define('VENDOR_DIR', APP_DIR . '/vendor');


// AUTOLOAD
$loader = require VENDOR_DIR . '/autoload.php';
$loader->setPsr4('App\\', APP_DIR . '/src');


use \KarmaFW\Kernel;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;
use \KarmaFW\App\Middlewares as KarmaMiddlewares;


ini_set('display_errors', 1);


// Build request
$request = Request::createFromGlobals();

// Init App and Define workflow
$app = new Kernel([
    new KarmaMiddlewares\TrafficLogger,
    new KarmaMiddlewares\ErrorHandler,
    new KarmaMiddlewares\ResponseTime,
    //new KarmaMiddlewares\MinimifierHtml,
    //new KarmaMiddlewares\RedirectToDomain('www.mydomain.com', ['mydomain.com', 'mydomain.fr', 'www.mydomain.fr']),
    //new KarmaMiddlewares\ForceHttps(302, ['www.mydomain.com']),
    //new KarmaMiddlewares\GzipEncoding,
    //new KarmaMiddlewares\MaintenanceMode,
    new KarmaMiddlewares\SessionHandler,
    //'Authentification',
    //new KarmaMiddlewares\CacheHtml(APP_DIR . '/var/cache/html', 3600),
    //new KarmaMiddlewares\CommandRouter($argv),
    //new KarmaMiddlewares\UrlGroupRouter,
    new KarmaMiddlewares\UrlRouter,
]);

// Process App workflow/pipe and return a $response
$response = $app->handle($request);

// Send $response->content to the client (browser or stdout)
$response->send();

