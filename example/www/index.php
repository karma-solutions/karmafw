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


// Init App and Define workflow
$app = new Kernel([
    new KarmaMiddlewares\TrafficLogger,
    new KarmaMiddlewares\ErrorHandler(true),
    new KarmaMiddlewares\ResponseTime,
    //new KarmaMiddlewares\MinimifierHtml,
    //new KarmaMiddlewares\RedirectToDomain('www.mydomain.com', ['mydomain.com', 'mydomain.fr', 'www.mydomain.fr']),
    //new KarmaMiddlewares\ForceHttps(302, ['www.mydomain.com']),
    //new KarmaMiddlewares\GzipEncoding,
    //new KarmaMiddlewares\MaintenanceMode,
    new KarmaMiddlewares\SessionHandler,
    //new KarmaMiddlewares\AuthentificationHandler, // (not implemented)
    //new KarmaMiddlewares\CacheHtml(APP_DIR . '/var/cache/html', 3600),
    //new KarmaMiddlewares\UrlGroupRouter,
    new KarmaMiddlewares\UrlRouter,
]);

$app->run();
