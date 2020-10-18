<?php

use \KarmaFW\Routing\Router;


// Homepage
Router::get('/', ['\\App\\Controllers\\HomeController', 'homepage'])->setName('homepage');


// login/logout
Router::get('/logout', ['\\App\\Controllers\\HomeController', 'logout'])->setName('logout');
Router::get('/login', ['\\App\\Controllers\\HomeController', 'login'])->setName('login');
Router::post('/login', ['\\App\\Controllers\\HomeController', 'login_post']);



// clients
Router::get('/clients', ['\\App\\Controllers\\Loggued\\ClientController', 'clients_list'])->setName('clients_list');
Router::get('/clients/nouveau-client', ['\\App\\Controllers\\Loggued\\ClientController', 'client_new'])->setName('client_new');
Router::get('/clients/([0-9]+)-([^/]+)$', ['\\App\\Controllers\\Loggued\\ClientController', 'client_edit'], 'regex', ['client_id', 'slug'])->setName('client_edit');
Router::post('/clients/save-client', ['\\App\\Controllers\\Loggued\\ClientController', 'client_save'])->setName('client_save');
Router::post('/clients/delete-client', ['\\App\\Controllers\\Loggued\\ClientController', 'client_delete'])->setName('client_delete');







// not route found => 404
Router::error404(['\\App\\Controllers\\ErrorController', 'error404']);


