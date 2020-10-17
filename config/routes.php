<?php

namespace KarmaFW\config;

use \KarmaFW\Routing\Router;


// Homepage
//Router::get('/', ['App\\Controllers\\HoomeController', 'homepage'])->setName('home');

// Assets minimifier
Router::get('(/assets/js/[^/]+.js).phpmin.js$', ['KarmaFW\\Controllers\\MinimifierController', 'minimifier_js'], 'regex', ['file_url'])->setName('minimifier_js');
Router::get('(/assets/css/[^/]+.css).phpmin.css$', ['KarmaFW\\Controllers\\MinimifierController', 'minimifier_css'], 'regex', ['file_url'])->setName('minimifier_css');
