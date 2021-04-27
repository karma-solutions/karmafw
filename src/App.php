<?php

namespace KarmaFW;

use \KarmaFW\Lib\Hooks\HooksManager;
//use \KarmaFW\Database\Sql\SqlDb;
//use \KarmaFW\Database\Sql\SqlOrmModel;

class App
{
	protected static $booted = false;
	protected static $helpers_dirs = [
		APP_DIR . "/src/helpers",
		FW_SRC_DIR . "/helpers",
	];

	public static $db = null;
	public static $data = [];


	/* #### */

	public static function boot()
	{

		define('FW_SRC_DIR', __DIR__);
		define('FW_DIR', __DIR__ . "/..");

		if (! defined('APP_DIR')) {
			echo "ERROR: Please, define APP_DIR" . PHP_EOL;
			exit(1);
		}


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
		//class_alias('\\KarmaFW\\Database\\Sql\\SqlDb', 'SqlDb');
		//class_alias('\\KarmaFW\\Database\\Sql\\SqlSchema', 'SqlSchema');
		//class_alias('\\KarmaFW\\Database\\Sql\\SqlTable', 'SqlTable');
		//class_alias('\\KarmaFW\\Database\\Sql\\SqlOrmModel', 'SqlOrmModel');
		//class_alias('\\KarmaFW\\Database\\Sql\\SqlQuery', 'SqlQuery');
		//class_alias('\\KarmaFW\\Database\\Sql\\SqlWhere', 'SqlWhere');
		//class_alias('\\KarmaFW\\Database\\Sql\\SqlExpr', 'SqlExpr');
		//class_alias('\\KarmaFW\\Database\\Sql\\SqlLike', 'SqlLike');
		//class_alias('\\KarmaFW\\Database\\Sql\\SqlIn', 'SqlIn');
		//class_alias('\\KarmaFW\\Database\\Sql\\SqlTools', 'SqlTools');
		

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

	

    public static function setData($key, $value=null)
	{
		self::$data[$key] = $value;
	}

	public static function getData($key, $default_value=null)
	{
		return array_key_exists($key, self::$data) ? self::$data[$key] : $default_value;
	}

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

	public static function loadHelpers($dir)
	{
		$helpers = glob($dir . '/helpers_*.php');

		foreach ($helpers as $helper) {
			require $helper;
		}

	}


	public static function isCli()
	{
		return (php_sapi_name() == 'cli');
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

			//$instances[$instance_name] = new SqlDb($dsn);

			$db = App::getData('app')->get('db');
			$instances[$instance_name] = $db($dsn);
		}

		return $instances[$instance_name];
	}


	public static function getConfig($key=null, $subkey=null)
	{
		$config = App::getData('config');
		if (is_null($key)) {
			return $config;
		}

		if (empty($config) || !isset($config[$key])) {
			return null;
		}

		if (is_null($subkey)) {
			return $config[$key];
		}

		if (empty($config[$key]) || !isset($config[$key][$subkey])) {
			return null;
		}

		return $config[$key][$subkey];
	}

}
