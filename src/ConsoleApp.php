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

		print_r($argv); // TODO: call defined class/method


		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('consoleapp.route.after', []);
		}


	}


}
