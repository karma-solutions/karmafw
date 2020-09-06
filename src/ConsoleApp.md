


## Structure d'une app console

```
bin
    app_console.php
config
    config.php
src
    Models
    helpers
vendor
```




## Console command
```
mkdir -p bin
nano bin/app_console.php
```

```
<?php

// CONFIG
define('APP_DIR', realpath(__DIR__ . '/..'));
define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));


require APP_DIR . '/config/config.php';


// AUTOLOAD
$loader = require VENDOR_DIR . '/autoload.php';
$loader->setPsr4('MyApp\\', __DIR__ . '/../src');



// APP BOOT
\KarmaFW\ConsoleApp::boot();


// YOUR INIT CODE HERE
\KarmaFW\ConsoleApp::getDb()->execute("set names utf8");


// APP ROUTE
\KarmaFW\ConsoleApp::routeFromArgs($argv);

```