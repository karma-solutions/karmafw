<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class AuthBasic
{
	protected $method;


	public function __construct($method='basic')
	{
		$this->method = $method;
	}


	public function __invoke(Request $request, Response $response, callable $next)
	{
		$is_auth = false;

		//pre($request, 1);

		if (! $is_auth) {
			//return $response->redirect( getRouteUrl('login') );
		}

		return $next($request, $response);
	}

}
