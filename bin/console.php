#!/usr/bin/php
<?php

// CONFIG
define('APP_DIR', realpath(__DIR__ . '/..'));
define('VENDOR_DIR', realpath(APP_DIR . '/vendor'));


// AUTOLOAD
$loader = require VENDOR_DIR . '/autoload.php';
$loader->setPsr4('App\\', __DIR__ . '/../src');


\KarmaFW\App::boot();


//\KarmaFW\App::getDb()->execute("set names utf8");


// APP ROUTE & GO
\KarmaFW\App::routeCommand($argv);
