<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class AuthentificationHandler
{
	
	public function __invoke(Request $request, Response $response, callable $next)
	{

		return $next($request, $response);
	}

}
