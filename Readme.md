
```
  _  __                          _______        __
 | |/ /__ _ _ __ _ __ ___   __ _|  ___\ \      / /
 | ' // _` | '__| '_ ` _ \ / _` | |_   \ \ /\ / / 
 | . \ (_| | |  | | | | | | (_| |  _|   \ V  V /  
 |_|\_\__,_|_|  |_| |_| |_|\__,_|_|      \_/\_/   
```


# Présentation

KarmaFW est un mini framework PHP qui gère le routing, les templates et les connexions aux bases SQL.


## Fonctionnalités

- Routing Web
- Templates PHP/HTML
- Connexions SQL
- FileUpload web
- Envoi d'emails (SMTP)
- Paiements: Paypal, Payplug, Stripe
- Auth: GoogleAuthenticator, SmsAuthenticator
- PDF: création de PDF (HTML to PDF)
- SMS: Envoi de SMS (Free & SmsEnvoi.com)
- Hooks PHP
- Bitly: génération d'url bit.ly


### Pré-requis

Composer est nécessaire afin de gérer les autoload des classes PHP.


## Structure d'une [app console](src/ConsoleApp.md)

```
bin
    app_console.php
config
    config.php
src
    scripts
        my_test_script.php
    Models
    helpers
vendor
    karmasolutions/karmafw
```


## Structure d'une [app web](src/)

```
config
    config.php
    routes.php
public
    .htaccess
    index.php
    images
    css
    js
    vendor
src
  Controllers
    MyAppController
    HomeController
  Models
    User
  helpers
    helpers_myapp.php
templates
    homepage.tpl.php
vendor
    karmasolutions/karmafw
```


# Configuration

Les paramètres de configuration de l'application se déclarent dans le fichier ./config/config.php

Le nom de l'application est à définir dans la variable APP_NAME.
```
define('APP_NAME', "Mon app PHP");
```

### [Routing](src/Routing/)

Les routes se déclarent dans le fichier ./config/routes.php
  
Chaque route est attribuée à la méthode d'un controller à renseigner.


### [Templates](src/Templates/)

Le chemin d'accès aux fichiers de templates se fait dans la variable de config TPL_DIR.
```
define('TPL_DIR', APP_DIR . '/templates');
```


### [Database SQL](src/Database/Sql/)

Les informations de connexions à MySQL se font dans la variable de config DB_DSN.
```
define('DB_DSN', 'mysql://user:password@localhost/db_name');
```


# Utilisation


## Création d'un nouveau projet

```
$ mkdir /var/www/my_app
$ cd /var/www/my_app
```


1) Composer
```
$ composer init
```

Ajouter ceci dans composer.json :
```
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


3) DocumentIndex
```
mkdir -p public
nano public/index.php
```

```
<?php

// CONFIG
define('APP_DIR', realpath(__DIR__ . '/..'));
define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));


// AUTOLOAD
$loader = require VENDOR_DIR . '/autoload.php';
$loader->setPsr4('MyApp\\', __DIR__ . '/../src');



// APP BOOT
\KarmaFW\WebApp::boot();


// YOUR INIT CODE HERE
\KarmaFW\WebApp::getDb()->execute("set names utf8");


// APP ROUTE
\KarmaFW\WebApp::route();

```


4) App config

Créer le fichier config/config.php et le remplir avec ceci :
```
mkdir -p config
nano config/config.php
```

```
<?php

ini_set('display_errors', 1);

define('APP_NAME', "MyAPP");

define('ENV', "dev");

define('TPL_DIR', APP_DIR . '/templates');

define('DB_DSN', 'mysql://root@localhost/myapp');
```

Créer le fichier config/routes.php et le remplir avec ceci :
```
nano config/routes.php
```

```
<?php

namespace MyApp\config;

use \KarmaFW\Routing\Router;


// Homepage
Router::get('/', ['MyApp\\Controllers\\HomeController', 'homepage'])->setName('home');

```


## 5) Homepage controller : src/Controllers/MyAppController.php
```
mkdir -p src/Controllers
nano src/Controllers/MyAppController.php
```

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
			$this->template->assign('user', $this->user);
		}
	}
}

```


## 6) Homepage controller : src/Controllers/HomeController.php
```
nano src/Controllers/HomeController.php
```

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

		$databases = $db->executeSelect('show databases');
		$this->template->assign('databases', $databases);

		$this->template->display('homepage.tpl.php');
	}
	
}

```


## 7) Homepage template : templates/homepage.tpl.php
```
mkdir -p templates
nano templates/homepage.tpl.php
```

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
nano templates/layout.tpl.php
```

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
nano templates/homepage2.tpl.php
```

```
{layout layout.tpl.php}

<pre>
<?php print_r($databases); ?>
</pre>

```



## Good practives

## Configuration
fichiers de conf (config.php & routes.php)
page 404
erreurs et exceptions (page 500)

### SEO
url rewriting
title, meta desc, h1, canonical
liens
robots.txt
sitemap.xml

### Performance
opcache, memcache/redis
cache de templates
minimify/combine css & js
gzip
etag + not_modified_304 + expire
cdn

### Stats
traffic logguer
google analytics
google webmaster tools
