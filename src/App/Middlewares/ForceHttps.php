<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;
use \KarmaFW\App\ResponseRedirect;


class ForceHttps
{
	protected $redirect_status = 302;


	public function __construct($redirect_status=302)
	{
		$this->redirect_status = $redirect_status;
	}


	public function __invoke(Request $request, Response $response, callable $next)
	{
		if (! $request->isSecure()) {
			$redirect_url = 'https://' . $request->SERVER['SERVER_NAME'] . $request->SERVER['REQUEST_URI'];
			return new ResponseRedirect($redirect_url, $this->redirect_status);
		}

		return $next($request, $response);
	}

}
