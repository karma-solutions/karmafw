<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class UrlPrefixRouter
{
	
	public function __construct()
	{
		// TODO
	}


	public function __invoke(Request $request, Response $response, callable $next)
	{
		// TODO

		return $next($request, $response);
	}

}
