<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class MaintenanceMode
{
	protected $maintenance_active = true;

	
	public function __construct($active=true)
	{
		$this->maintenance_active = $active;
	}


	public function __invoke(Request $request, Response $response, callable $next)
	{
		if (! $this->maintenance_active) {
			return $next($request, $response);
		}

		$content = '<html><head><title>Service en maintenance</title><head><body><h1>Service en maintenance</h1></body></html>';

		$response = new ResponseError(503, $content);
		return $response;
	}

}
