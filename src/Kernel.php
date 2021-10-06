<?php

namespace KarmaFW;

use \KarmaFW\App;
use \KarmaFW\App\Container;
use \KarmaFW\App\Pipe;
use \KarmaFW\App\Tools;
use \KarmaFW\Database\Sql\SqlDb;
use \KarmaFW\Database\Redis\Redis;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;
use \KarmaFW\Routing\Router;


define('FW_SRC_DIR', __DIR__);
define('FW_DIR', realpath(__DIR__ . '/..'));

if (! defined('APP_DIR')) {
	echo "ERROR: Please, define APP_DIR" . PHP_EOL;
	exit(1);
}


class Kernel
{
	protected static $booted = false;
	protected static $helpers_dirs = [
		APP_DIR . "/src/helpers",
		FW_SRC_DIR . "/helpers",
	];

	protected $db = null; // TODO: a deplacer dans $services['db']
	protected $redis = null; // TODO: a deplacer dans $services['redis']

	protected $middlewares;
	protected $container;


	public function __construct($middlewares=[])
	{
		$this->middlewares = $middlewares;
		$this->container = new Container;

		App::setData('app', $this);

		try {
			$this->configure();
			$this->init();

		} catch (\Exception $e) {
			header("HTTP/1.0 500 Internal Server Error");
			echo "<h1>Internal Server error</h1>";

			if (ENV === 'dev') {
				echo "<pre>";
				print_r($e);
				echo "</pre>";
			}
			exit;
		}

	}

	
	public function configure()
	{
		if (is_file(APP_DIR . '/config/config.php')) {
			require APP_DIR . '/config/config.php';
		}


		if (! defined('APP_NAME')) {
			define('APP_NAME', "PHP Application");
		}

		if (! defined('TPL_DIR')) {
			//define('TPL_DIR', APP_DIR . '/templates');
		}

		if (! defined('ENV')) {
			$env = defined('ENVIRONMENT') ? ENVIRONMENT : 'prod';
			define('ENV', $env);
		}

		if (! defined('DB_DSN')) {
			//define('DB_DSN', 'mysql://root@localhost/my_app');
		}

		if (! defined('ERROR_TEMPLATE')) {
			//define('ERROR_TEMPLATE', "page_error.tpl.php");
		}


		// Load config files
		$config_files = glob(APP_DIR . '/config/config-*.php');
		$config = [];
		foreach ($config_files as $config_file) {
			$config_file_config = require($config_file);
			$config_file_basename = basename($config_file);
			$config_file_name = substr($config_file_basename, 7, -4);
			$config[$config_file_name] = $config_file_config;
			//print_r($config_file_name);
		}
		//echo "<pre>";print_r($config); exit;
		App::setData('config', $config);


	}


	public function init()
	{
		// Load helpers
		Tools::loadHelpers(APP_DIR . '/src/helpers');
		Tools::loadHelpers(FW_DIR . '/src/helpers');


		// Load services
		$this->loadServices();


		if (defined('DB_DSN')) {
			//$this->db = static::getDb('default', DB_DSN);
			//$this->db = $this->connectDb('default', DB_DSN); // TODO: a deplacer dans $services['db'] ( ou $services['sql'] ? )
		}

		if (defined('REDIS_DSN')) {
			//$this->redis = new Redis(REDIS_DSN); // TODO: a deplacer dans $services['redis']
		}
		
	}



	public function run()
	{
		// 1) Read input
		if (Tools::isCli()) {
			$request = Request::createFromArgv();

		} else {
			$request = Request::createFromGlobals();
		}

		// 2) Process App workflow/pipe and return a $response
		$response = $this->handle($request);

		// 3) Send $response->content to the client (browser or stdout)
		return $response->send();
	}



	/* MAIN APP PROCESS */

	public function handle(Request $request=null)
	{
		try {
			$response = new Response(200, [], null);
			$pipe = new Pipe($this->middlewares);

			$response = $pipe->next($request, $response);

		} catch (\Exception $e) {
            $error_code = $e->getCode();
            $error_message = $e->getMessage();

            error_log("[App Error] " . $error_code . " : " . $error_message);


			// TODO: voir comment bien injecter cette dependance
			$debugbar = App::getData('debugbar');
			if ($debugbar) {
	            if (isset($debugbar['exceptions'])) {
					$debugbar['exceptions']->addException($e);
	            }
	        }


            $content = null;
            if (ENV == 'dev') {
                $title = "App CATCHED EXCEPTION CODE " . $error_code;
                $message = '<pre>' . print_r($e, true) . '</pre>';
                $content = '<title>' . $title . '</title><h1>' . $title . '</h1><h2>' . $error_message . '</h2><p>' . $message . '</p>';

            } else {
				$title = "Server Error";
				$message = "";
				$content = '<title>' . $title . '</title><h1>' . $title . '</h1><h2>An error has occured</h2><p>' . $message . '</p>';
			}

            //throw $e;
            $response->html($content, 500);
		}

		return $response;
	}

    
    /*
	public function setService($service_name, $callback)
	{
		return $this->set($service_name, $callback);
	}
	*/

	public function loadServices()
	{
		// TODO: rendre parametrable la liste des services

		$this->set('router', function (Request $request, Response $response) {
			return Router::routeRequest($request, $response);
		});

		$this->set('db', function ($dsn=null) {
			if (empty($dsn) && defined('DB_DSN')) {
				$dsn = DB_DSN;
			}
			if (empty($dsn)) {
				$dsn = App::getConfig('database', 'default');
			}
			//pre($config_database, 1);
			return new \KarmaFW\Database\Sql\SqlDb($dsn);
		});

		$this->set('redis', function ($dsn=null) {
			if (empty($dsn) && defined('REDIS_DSN')) {
				$dsn = REDIS_DSN;
			}
			return new \KarmaFW\Database\Redis\Redis($dsn);
		});

		$this->set('template', function ($tpl=null, $data=[]) {
			//return new \KarmaFW\Templates\PhpTemplate($tpl, $data);
			return new \KarmaFW\Templates\LightweightTemplate($tpl, $data);
		});

		$this->set('traffic_logger', function (Request $request, Response $response) {
			return null; // TODO
		});
	}


    /* CONTAINER */


	// GET an element from the container
	public function get($key, $default_value=null)
	{
		return isset($this->container[$key]) ? $this->container[$key] : $default_value;
	}

	// STORE an element to the container
	public function set($key, $value=null)
	{
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				$this->set($k, $v);
			}
			
		} else {
			$this->container[$key] = $value;
		}
		return $this;
	}

    public function has($name)
    {
        return isset($this->container[$name]);
    }



    /* DATABASE */

	public function connectDb($instance_name=null, $dsn=null)
	{
		/*
		$dsn = 'mysql://user:pass@localhost/my_app';
		*/
		static $instances = [];
		//static $last_instance_name = null;

		if (empty($instance_name)) {
			if (! empty($this->db)) {
				return $this->db;
			}

			$instance_name = 'default';

			//if (! empty($last_instance_name)) {
			//	$instance_name = $last_instance_name;
			//}
		}

		//$last_instance_name = $instance_name;

		if (empty($instances[$instance_name])) {
			if (empty($dsn) && defined('DB_DSN')) {
				$dsn = DB_DSN;
			}
			if (empty($dsn)) {
				$dsn = App::getConfig('database', $instance_name);
			}
			$instances[$instance_name] = new SqlDb($dsn);
		}

		return $instances[$instance_name];
	}

    public function getDb()
    {
    	return $this->db;
    }

    public function setDb($db)
    {
    	$this->db = $db;
    	return $this;
    }

}

