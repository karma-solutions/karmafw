<?php

// CONFIG
define('APP_DIR', realpath(__DIR__ . '/..'));
define('VENDOR_DIR', realpath(APP_DIR . '/vendor'));


// AUTOLOAD
$loader = require VENDOR_DIR . '/autoload.php';
$loader->setPsr4('App\\', __DIR__ . '/../src');


// ERRORS HANDLER
//$whoops = new \Whoops\Run;
//$whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
//$whoops->register();


// LOAD ROUTES
require APP_DIR . '/config/routes.php';


// DEFINE HOOKS
//\KarmaFW\Hooks\Lib\HooksManager::addHookAction('webcontroller__init', function ($controller) {
//	echo "webcontroller hooked<hr />";
//});



// YOUR INIT CODE HERE (before App::boot)


// APP BOOT
\KarmaFW\WebApp::boot();


// YOUR INIT CODE HERE (after App::boot)

//\KarmaFW\WebApp::getDb()->execute("set names utf8"); // set mysql UTF8



// APP ROUTE & GO
\KarmaFW\WebApp::routeUrl();

