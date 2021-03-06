<?php

namespace KarmaFW\Routing\Controllers;

use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;
use \KarmaFW\Lib\Hooks\HooksManager;


class AppController
{
	protected $db = null;


	public function __construct(Request $request, Response $response)
	{

		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('appcontroller.before', [$this]);
		}

		if (defined('DB_DSN')) {
			$this->db = App::getDb();
		}

		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('appcontroller.after', [$this]);
		}

		//echo "DEBUG " . __CLASS__ . ": controller instanced<hr />" . PHP_EOL;
	}


}
