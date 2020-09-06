<?php

namespace KarmaFW;

use \KarmaFW\Routing\Router;
use \KarmaFW\Lib\Hooks\HooksManager;


class ConsoleApp extends App
{
	public static $session_user = false; // user connected with a session
	public static $controller = null;


	public static function boot()
	{
		parent::boot();

		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('consoleapp.boot.before', []);
		}


		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('consoleapp.boot.after', []);
		}

	}


	public static function routeFromArgs($argv=[])
	{
		if (! self::$booted) {
			self::boot();
		}

		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('consoleapp.route.before', []);
		}

		$bin_path = array_shift($argv);

		if ( ($command = array_shift($argv)) === null ) {
			// error: missing command parameter
			throw new Exception("Command not specified", 1);
		
		} else {
			$scripts_dir = APP_DIR . "/src/scripts";
			$script_filepath = $scripts_dir . '/' . $command . '.php';

			if (is_file($script_filepath)) {
				require $script_filepath;

			} else {
				throw new Exception("Script file not found", 1);
			}

		}



		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('consoleapp.route.after', []);
		}


	}


}
