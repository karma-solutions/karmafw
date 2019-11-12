<?php

namespace KarmaFW;

use KarmaFW\Routing\Router;
use KarmaFW\Hooks\HooksManager;
use KarmaFW\Database\Sql\SqlDb;
use \KarmaFW\Database\Sql\SqlOrmModel;
use KarmaFW\Templates\Templater;


define('FW_SRC_DIR', __DIR__);
define('FW_DIR', __DIR__ . "/..");

if (! defined('APP_DIR')) {
	echo "ERROR: Please, define APP_DIR" . PHP_EOL;
	exit(1);
}


class App
{
	protected static $booted = false;

	public static function boot()
	{
		HooksManager::applyHook('fw_app_boot__before', []);

		// include helpers
		self::loadHelpers(FW_SRC_DIR . "/../helpers");

		self::$booted = true;
		HooksManager::applyHook('fw_app_boot__after', []);
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
		if (! self::$booted) {
			self::boot();
		}

		// routing: parse l'url puis transfert au controller

		$route = Router::routeByUrl( $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], false );

		if ($route) {
			//echo "success: route ok";
			exit(0);

		} else if ($route === null) {
			// route found but callback is not callable
			HooksManager::applyHook('fw_bootstrap_404', []);
			errorHttp(404, 'Warning: route callback is not callable', '404 Not Found');
			exit(1);

		} else if ($route === true) {
			// route found but no callback defined
			HooksManager::applyHook('fw_bootstrap_404', []);
			errorHttp(404, "Warning: route found but no callback defined", '404 Not Found');
			exit(1);

		} else if ($route === false) {
			// no matching route
			HooksManager::applyHook('fw_bootstrap_404', []);
			errorHttp(404, "Warning: no matching route", '404 Not Found');
			exit(1);

		} else {
			exit(1);
		}

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


	public static function createTemplate()
	{
		return new Templater();
	}


	public static function createOrmModel($db, $table_name, $primary_key_values=[])
	{
		return new SqlOrmModel($db, $table_name, $primary_key_values);
	}

}
