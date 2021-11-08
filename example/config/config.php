<?php

ini_set('display_errors', 1);


define('APP_NAME', "Demo APP");

define('ENV', "dev");

define('DB_DSN', 'mysql://demo:demo@localhost/demo?charset=UTF8');

define('TPL_DIR', APP_DIR . '/templates');
define('TPL_CACHE_DIR', APP_DIR . '/var/cache/templates');
define('ERROR_TEMPLATE', "page_error.tpl");

