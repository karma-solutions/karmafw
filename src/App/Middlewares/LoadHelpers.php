<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;


class LoadHelpers
{
	protected $helpers_dirs = [
		FW_SRC_DIR . "/helpers",
		APP_DIR . "/src/helpers",
	];
	
	
	public function __invoke(Request $request, Response $response, callable $next)
	{
		foreach ($this->helpers_dirs as $helpers_dir) {
			$this->loadHelpers($helpers_dir);
		}

		return $next($request, $response);
	}


	protected function loadHelpers($dir)
	{
		$helpers = glob($dir . '/helpers_*.php');

		foreach ($helpers as $helper) {
			require $helper;
		}
	}

}
