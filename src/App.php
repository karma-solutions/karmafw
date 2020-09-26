<?php

namespace KarmaFW;

use \KarmaFW\Routing\Router;
use \KarmaFW\Lib\Hooks\HooksManager;
use \KarmaFW\Database\Sql\SqlDb;
use \KarmaFW\Database\Sql\SqlOrmModel;
use \KarmaFW\Templates\PhpTemplate;


define('FW_SRC_DIR', __DIR__);
define('FW_DIR', __DIR__ . "/..");

if (! defined('APP_DIR')) {
	echo "ERROR: Please, define APP_DIR" . PHP_EOL;
	exit(1);
}


class App
{
	protected static $booted = false;
	protected static $session_user = false; // user connected with a session
	protected static $helpers_dirs = [FW_SRC_DIR . "/../helpers"];

	public static function boot()
	{
		HooksManager::applyHook('app_boot__before', []);

		// start session
		if (empty(session_id())) {
			if (defined('SESSION_NAME') && ! empty(SESSION_NAME)) {
				session_name(SESSION_NAME);
			}

			if (defined('SESSION_DURATION') && is_numeric(SESSION_DURATION)) {
				ini_set('session.gc_maxlifetime', SESSION_DURATION);
				session_set_cookie_params(SESSION_DURATION);
				// Note: si cron est actif, il faut modifier la valeur de session.gc_maxlifetime dans /etc/php/7.3/apache2/php.ini (voir /etc/cron.d/php)
			}

			session_start();
		}

		// move fw_helpers at the end of the list (to be loaded the last one)
		if (count(self::$helpers_dirs) > 1) {
		$fw_helpers = array_shift(self::$helpers_dirs);
		self::$helpers_dirs[] = $fw_helpers;
		}

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
		

		self::$booted = true;
		HooksManager::applyHook('app_boot__after', []);
	}	


	public static function registerHelpersDir($dir)
	{
		self::$helpers_dirs[] = $dir;
	}

	protected static function loadHelpers($dir)
	{
		$helpers = glob($dir . '/helpers_*.php');

		foreach ($helpers as $helper) {
			require $helper;
		}
	}


	public static function route()
	{
		return self::routeUrl();
	}


	public static function routeUrl()
	{
		if (! self::$booted) {
			self::boot();
		}

		// routing: parse l'url puis transfert au controller

		HooksManager::applyHook('app_route__before', []);

		$route = Router::routeByUrl( $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], false );

		HooksManager::applyHook('app_route__after', [$route]);

		if ($route) {
			//echo "success: route ok";
			exit(0);

		} else if ($route === null) {
			// route found but callback is not callable
			HooksManager::applyHook('app_route_404', []);
			errorHttp(404, 'Warning: route callback is not callable', '404 Not Found');
			exit(1);

		} else if ($route === 0) {
			// route found but no callback defined
			HooksManager::applyHook('app_route_404', []);
			errorHttp(404, "Warning: route found but no callback defined", '404 Not Found');
			exit(1);

		} else if ($route === false) {
			// no matching route
			HooksManager::applyHook('app_route_404', []);
			errorHttp(404, "Warning: no matching route", '404 Not Found');
			exit(1);

		} else {
			// other cases
			HooksManager::applyHook('app_route_404', []);
			errorHttp(404, "Warning: cannot route", '404 Not Found');
			exit(1);
		}

	}


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
				echo "Usage: php console.php <action> [arguments]" . PHP_EOL . PHP_EOL;
				echo "Warning: invalid action" . PHP_EOL;
			}

		} else {
			echo "PHP Console script" . PHP_EOL . PHP_EOL; 
			echo "Usage: php console.php <action> [arguments]" . PHP_EOL . PHP_EOL;
			echo "Warning: missing action" . PHP_EOL;
		}

		exit(1);
	}


	public static function getDb($instance_name=null, $dsn=null)
	{
		/*
		$dsn = 'mysql://user:pass@localhost/my_app';
		*/
		static $instances = [];
		static $last_instance_name = null;

		if (empty($instance_name)) {
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


	public static function createTemplate($tpl_path=null, $variables=[], $layout=null, $templates_dirs=null)
	{
		return new PhpTemplate($tpl_path, $variables, $layout, $templates_dirs);
	}


	public static function createOrmItem($table_name, $primary_key_values=[], $db=null)
	{
		return new SqlOrmModel($table_name, $primary_key_values, $db);
	}
	

	public static function getUser()
	{
		return self::$session_user;
	}

	public static function setUser($user)
	{
		self::$session_user = $user;
	}

}
