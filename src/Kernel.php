<?php

namespace KarmaFW;

use \KarmaFW\App\Container;
use \KarmaFW\App\Pipe;
use \KarmaFW\App\Tools;
use \KarmaFW\Database\Sql\SqlDb;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


define('FW_SRC_DIR', __DIR__);
define('FW_DIR', __DIR__ . "/..");

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

	protected $db = null;

	protected $middlewares;
	protected $container;


	public function __construct($middlewares=[])
	{
		$this->middlewares = $middlewares;
		$this->container = new Container;

		try {
			$this->configure();

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


		if (defined('DB_DSN')) {
			//$this->db = static::getDb('default', DB_DSN);
			$this->db = $this->connectDb('default', DB_DSN);
		}


		// Load helpers
		Tools::loadHelpers(APP_DIR . '/src/helpers');
		Tools::loadHelpers(FW_DIR . '/src/helpers');
	}



	/* MAIN APP PROCESS */

	public function handle(Request $request)
	{
		try {
			$response = new Response(200, [], null);
			$pipe = new Pipe($this->middlewares);

			$response = $pipe->next($request, $response);

		} catch (\Exception $e) {
            $error_code = $e->getCode();
            $error_message = $e->getMessage();

            error_log("[App] Error " . $error_code . " : " . $error_message);

            $content = null;
            if (ENV == 'dev') {
                $title = "App CATCHED EXCEPTION CODE " . $error_code;
                $message = '<pre>' . print_r($e, true) . '</pre>';
                $content = '<title>' . $title . '</title><h1>' . $title . '</h1><h2>' . $error_message . '</h2><p>' . $message . '</p>';
            }

            //throw $e;
            $response->setHtml($content, 500);
		}

		return $response;
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

