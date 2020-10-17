<?php

namespace KarmaFW;

use \KarmaFW\Routing\Router;
use \KarmaFW\Lib\Hooks\HooksManager;
use \KarmaFW\Database\Sql\SqlDb;
//use \KarmaFW\Database\Sql\SqlOrmModel;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;
use \KarmaFW\App\Pipe;
use \KarmaFW\App\Container;


define('FW_SRC_DIR', __DIR__);
define('FW_DIR', __DIR__ . "/..");

if (! defined('APP_DIR')) {
	echo "ERROR: Please, define APP_DIR" . PHP_EOL;
	exit(1);
}


class App
{
	protected static $booted = false;
	protected static $helpers_dirs = [
		APP_DIR . "/src/helpers",
		FW_SRC_DIR . "/helpers",
	];

	public static $db = null;
	public static $data = [];

	protected static $instance = null;
	protected $middlewares;
	protected $container;


	public function __construct($middlewares=[])
	{
		$this->middlewares = $middlewares;
		$this->container = new Container;
		self::$instance = $this;

		try {
			$this->configure();

			self::loadHelpersDirs();

		} catch (\Exception $e) {
			header("HTTP/1.0 500 Internal Server Error");
			echo "<h1>Server error</h1>";

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
			self::$db = static::getDb('default', DB_DSN);
			//self::$db->execute("set names utf8");
		}

	}


	public function handle($request)
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
            $response->setStatus(500)->setHtml($content);
		}

		return $response;
	}


	/* #### */

	public static function boot()
	{
		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('app.boot.before', []);
		}

		// TODO: config à migrer dans un fichier .env et .env.prod et .env.dev et .env.local (à charger dans cet ordre, avec overwrite)
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
			define('ENV', 'prod');
		}

		if (! defined('DB_DSN')) {
			//define('DB_DSN', 'mysql://root@localhost/my_app');
		}

		if (! defined('ERROR_TEMPLATE')) {
			//define('ERROR_TEMPLATE', "page_error.tpl.php");
		}


		// move fw_helpers at the end of the list (to be loaded the last one)
		/*
		if (count(self::$helpers_dirs) > 1) {
			$fw_helpers = array_shift(self::$helpers_dirs);
			self::$helpers_dirs[] = $fw_helpers;
		}
		*/

		// include helpers
		foreach (self::$helpers_dirs as $helpers_dir) {
			self::loadHelpers($helpers_dir);
		}

		// define class aliases
		class_alias('\\KarmaFW\\App', 'App');
		class_alias('\\KarmaFW\\Database\\Sql\\SqlDb', 'SqlDb');
		class_alias('\\KarmaFW\\Database\\Sql\\SqlSchema', 'SqlSchema');
		class_alias('\\KarmaFW\\Database\\Sql\\SqlTable', 'SqlTable');
		class_alias('\\KarmaFW\\Database\\Sql\\SqlOrmModel', 'SqlOrmModel');
		class_alias('\\KarmaFW\\Database\\Sql\\SqlQuery', 'SqlQuery');
		class_alias('\\KarmaFW\\Database\\Sql\\SqlWhere', 'SqlWhere');
		class_alias('\\KarmaFW\\Database\\Sql\\SqlExpr', 'SqlExpr');
		class_alias('\\KarmaFW\\Database\\Sql\\SqlLike', 'SqlLike');
		class_alias('\\KarmaFW\\Database\\Sql\\SqlIn', 'SqlIn');
		class_alias('\\KarmaFW\\Database\\Sql\\SqlTools', 'SqlTools');
		

		if (defined('DB_DSN')) {
			self::$db = static::getDb();
		}


		// ERRORS HANDLER   // NOTE => a déplacer dans \KarmaFW\WebApp::boot() ??
		if (defined('ENV') && ENV == 'dev') {
			$whoops = new \Whoops\Run;
			$whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
			$whoops->register();
		}


		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('app.boot.after', []);
		}


		self::$booted = true;
	}	


	public static function registerHelpersDir($dir)
	{
		$dir = rtrim($dir, '/');
		if (! in_array($dir, self::$helpers_dirs)) {
			self::$helpers_dirs[] = $dir;
		}
	}

	
	public function get($key, $default_value=null)
	{
		return isset($this->container[$key]) ? $this->container[$key] : $default_value;
	}

	public function set($key, $value)
	{
		$this->container[$key] = $value;
	}

    public function has($name)
    {
        return isset($this->container[$name]);
    }


    // DEPRECATED
	public static function setData($key, $value=null)
	{
		self::$data[$key] = $value;
	}

	// DEPRECATED
	public static function getData($key, $default_value=null)
	{
		return array_key_exists($key, self::$data) ? self::$data[$key] : $default_value;
	}

	// DEPRECATED
	public static function hasData($key)
	{
		return array_key_exists($key, self::$data);
	}


	public static function unregisterHelpersDir($dir)
	{
		$dir = rtrim($dir, '/');
		$k = array_search($dir, self::$helpers_dirs);
		if ($k !== false) {
			unset(self::$helpers_dirs[$k]);
		}
	}


	protected static function loadHelpersDirs()
	{
		if (is_array(self::$helpers_dirs)) {
			foreach (self::$helpers_dirs as $helpers_dir) {
				self::loadHelpers($helpers_dir);
			}
		}
	}

	protected static function loadHelpers($dir)
	{
		$helpers = glob($dir . '/helpers_*.php');

		foreach ($helpers as $helper) {
			//echo '<li>'.$helper.'</li>';
			require $helper;
		}

	}


	public static function isCli()
	{
		return (php_sapi_name() == 'cli');
	}


	public static function getApp()
	{
		if (isset(self::$instance)) {
			return self::$instance;
		}
		throw new Exception("App is not instancied", 1);
	}


	public static function getDb($instance_name=null, $dsn=null)
	{
		/*
		$dsn = 'mysql://user:pass@localhost/my_app';
		*/
		static $instances = [];
		static $last_instance_name = null;

		if (empty($instance_name)) {
			if (! empty(self::$db)) {
				return self::$db;
			}

			$instance_name = 'default';

			//if (! empty($last_instance_name)) {
			//	$instance_name = $last_instance_name;
			//}
		}

		$last_instance_name = $instance_name;

		if (empty($instances[$instance_name])) {
			if (empty($dsn) && defined('DB_DSN')) {
				$dsn = DB_DSN;
			}
			$instances[$instance_name] = new SqlDb($dsn);
		}

		return $instances[$instance_name];
	}


	/*
	public static function createOrmItem($table_name, $primary_key_values=[], $db=null)
	{
		return new SqlOrmModel($table_name, $primary_key_values, $db);
	}
	*/


	/*
	public static function routeCommand($argv)
	{
		if (! self::$booted) {
			self::boot();
		}

		$arguments = array_slice($argv, 0);
		$script_name = array_shift($arguments);
		$command_name = array_shift($arguments);
		$class_name = implode('', array_map('ucfirst', explode("_", $command_name)));

		if (! empty($class_name)) {
			$class_user = '\\App\\Commands\\' . $class_name;
			$class_fw = '\\KarmaFW\\Commands\\' . $class_name;

			if (class_exists($class_user)) {
				$command = new $class_user;
				$command->run($arguments);
				exit(0);

			} else if (class_exists($class_fw)) {
				$command = new $class_fw;
				$command->run($arguments);
				exit(0);

			} else {
				echo "PHP Console script" . PHP_EOL . PHP_EOL; 
				echo "Usage: php console.php <command> [arguments]" . PHP_EOL . PHP_EOL;
				echo "Warning: invalid command" . PHP_EOL;
			}

		} else {
			echo "PHP Console script" . PHP_EOL . PHP_EOL; 
			echo "Usage: php console.php <command> [arguments]" . PHP_EOL . PHP_EOL;
			echo "Warning: missing command" . PHP_EOL;
		}

		exit(1);
	}
	*/

}
