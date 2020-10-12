<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;


class GzipEncoding
{
	
	public function __invoke(Request $request, Response $response, callable $next)
	{

		return $next($request, $response);
	}

}
