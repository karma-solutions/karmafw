
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
$loader->setPsr4('App\\', __DIR__ . '/../src');


// ERRORS HANDLER
$whoops = new \Whoops\Run;
$whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();


// ROUTE
require APP_DIR . '/config/routes.php';


\KarmaFW\App::registerHelpersDir(APP_DIR . '/src/helpers');

// APP BOOT & ROUTE
\KarmaFW\App::boot();
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

namespace App\config;

use \KarmaFW\Routing\Router;


// Homepage
Router::get('/', ['App\\Controllers\\AppController', 'homepage'])->setName('home');

```

## 5) Homepage controller : src/Controllers/AppController.php
```
<?php

namespace App\Controllers;

use \KarmaFW\App;
use \KarmaFW\Routing\Controllers\WebController;


class AppController extends WebController
{

	public function homepage()
	{
		$db = App::getDb();
		$db->connect();

		$rs = $db->execute('show databases');
		$databases = $rs->fetchAll();

		$template = App::createTemplate();
		$template->assign('title', 'My APP');
		$template->assign('databases', $databases);
		$template->display('homepage.tpl.php');
	}
	
}

```


## 6) Homepage template : templates/homepage.tpl.php
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
