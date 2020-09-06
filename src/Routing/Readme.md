

Exemple de fichier de routes ( ./config/routes.php )

```
use \KarmaFW\Routing\Router;


// Homepage
Router::get('/', ['App\\Controllers\\PublicController', 'homepage'])->setName('home');


// login/logout
Router::get('/logout', ['App\\Controllers\\PublicController', 'logout'])->setName('logout');
Router::get('/login', ['App\\Controllers\\PublicController', 'login'])->setName('login');
Router::post('/login', ['App\\Controllers\\PublicController', 'login_post']);


// clients (example)
Router::get('/clients', ['App\\Controllers\\ClientController', 'clients_list'])->setName('clients_list');
Router::get('/clients/nouveau-client', ['App\\Controllers\\ClientController', 'client_new'])->setName('client_new');
Router::get('/clients/([0-9]+)-([^/]+)$', ['App\\Controllers\\ClientController', 'client_edit'], 'regex', ['client_id', 'slug'])->setName('client_edit');
Router::post('/clients/save-client', ['App\\Controllers\\ClientController', 'client_save'])->setName('client_save');
Router::delete('/clients/delete-client', ['App\\Controllers\\ClientController', 'client_delete'])->setName('client_delete');



// 404
Router::get('.*', ['App\\Controllers\\ErrorController', 'error404'], 'regex');

```

