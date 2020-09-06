
# Présentation

KarmaFW est un mini framework PHP qui gère le routing, les templates et les connexions aux bases SQL.

## Configuration

Les paramètres de configuration de l'application se déclarent dans le fichier ./config/config.php

Le nom de l'application est à définir dans la variable APP_NAME.
```
define('APP_NAME', "Mon app PHP");
```


### Pré-requis

Composer est nécessaire afin de gérer les autoload des classes PHP.


### Routing

Les routes se déclarent dans le fichier ./config/routes.php
  
Chaque route est attribuée à la méthode d'un controller à renseigner.


### Templates

Le chemin d'accès aux fichiers de templates se fait dans la variable de config TPL_DIR.
```
define('TPL_DIR', APP_DIR . '/templates');
```


### Database

Les informations de connexions à MySQL se font dans la variable de config DB_DSN.
```
define('DB_DSN', 'mysql://user:password@localhost/db_name');
```


## Structure du projet web

```
config
	config.php
	routes.php
public
	index.php
	.htaccess
src
  Controllers
  	MyAppController
  	HomeController
  Models
  	User
  helpers
templates
	homepage.tpl.php
vendor
	karmasolutions/karmafw
```


# Nouveau projet


## 0) se positionner dans le dossier du projet

## 1) lancer `composer init`

## 2) modifier composer.json

```
# Ajouter ceci dans composer.json

{
    "repositories": [
        {
            "type": "path",
            "url": "/put/here/the/path/to/karmafw"
        }
    ],
    "require": {
        "karmasolutions/karmafw": "dev-master",
        "filp/whoops": "^2.2@dev"
    }
}
```


## 3) créer le dossier public et le fichier public/index.php et le remplir avec ceci :
```
<?php

// CONFIG
define('APP_DIR', realpath(__DIR__ . '/..'));
define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));

require APP_DIR . '/config/config.php';


// AUTOLOAD
$loader = require VENDOR_DIR . '/autoload.php';
$loader->setPsr4('MyApp\\', __DIR__ . '/../src');


// ERRORS HANDLER
$whoops = new \Whoops\Run;
$whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();


// ROUTE
require APP_DIR . '/config/routes.php';


\KarmaFW\App::registerHelpersDir(APP_DIR . '/src/helpers');

// APP BOOT
\KarmaFW\App::boot();


// YOUR INIT CODE HERE
\KarmaFW\WebApp::getDb()->execute("set names utf8");


// APP ROUTE
\KarmaFW\App::route();

```


## 4) créer le dossier config

## 4a) créer le fichier config/config.php et le remplir avec ceci :
```
<?php

ini_set('display_errors', 1);

define('TPL_DIR', APP_DIR . '/templates');

define('DB_DSN', 'mysql://root@localhost/myapp');

define('APP_NAME', "MyAPP");

```

## 4b) créer le fichier config/routes.php et le remplir avec ceci :
```
<?php

namespace MyApp\config;

use \KarmaFW\Routing\Router;


// Homepage
Router::get('/', ['MyApp\\Controllers\\HomeController', 'homepage'])->setName('home');

```

## 5) Homepage controller : src/Controllers/MyAppController.php
```
<?php

namespace MyApp\Controllers;

use \KarmaFW\App;
use \KarmaFW\Routing\Controllers\WebAppController;
use \MyApp\Models\User;


class MyAppController extends WebAppController
{
	protected $db;
	protected $user;

	public function __construct($request_uri=null, $request_method=null, $route=null)
	{
		parent::__construct($request_uri, $request_method, $route);

		$this->db = App::getDb();

		if (! empty($this->user_id)) {
			$this->user = User::load($this->user_id);
			$this->template->assign('databases', $databases);
		}
	}
}

```


## 6) Homepage controller : src/Controllers/HomeController.php
```
<?php

namespace MyApp\Controllers;

use \KarmaFW\App;


class HomeController extends MyAppController
{

	public function homepage()
	{
		$this->template->assign('title', 'My APP');

		$db = App::getDb();
		$db->connect();

		$rs = $db->execute('show databases');
		$databases = $rs->fetchAll();
		$this->template->assign('databases', $databases);

		$this->template->display('homepage.tpl.php');
	}
	
}

```


## 7) Homepage template : templates/homepage.tpl.php
```
<html>
<head>
	<title>{$title}</title>
</head>
<body>
<h1>hello world</h1>
<pre>
<?php print_r($databases); ?>
</pre>
</body>
</html>

```



## 8a) Layout : templates/layout.tpl.php
```
<html>
<head>
	<title>{$title}</title>
</head>
<body>
<h1>hello world</h1>

{$child_content}

</body>
</html>

```

## 8b) Homepage template avec layout : templates/homepage2.tpl.php
```
{layout layout.tpl.php}

<pre>
<?php print_r($databases); ?>
</pre>

```
