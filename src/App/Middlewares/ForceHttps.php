<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;


class ForceHttps
{
	
	public function __invoke(Request $request, Response $response, callable $next)
	{
		/*
		print_r($request); throw new Exception("DEBUG ME", 1);
		

		$is_ssl = false; // TODO
		if (! $is_ssl) {
			$redirect_url = 'https://' . $request->SERVER['SERVER_NAME'] . $request->SERVER['REQUEST_URI'] . (empty($request->SERVER['QUERY_STRING']) ? '' : ('?' . $request->SERVER['QUERY_STRING']));
			$status = 302;
			return new ResponseRedirect($redirect_url, $status);
		}
		*/

		return $next($request, $response);
	}

}
