<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class ForceHttps
{
	protected $redirect_status = 302;
	protected $redirect_domains = []; // example.com, www.example.com, example.fr, www.example.fr


	public function __construct($redirect_status=302, $redirect_domains=[])
	{
		$this->redirect_status = $redirect_status;
		$this->redirect_domains = $redirect_domains;
	}


	public function __invoke(Request $request, Response $response, callable $next)
	{
		if (! $request->isSecure()) {
			
			if (empty($this->redirect_domains) || in_array($request->SERVER['SERVER_NAME'], $this->redirect_domains)) {
				$redirect_url = 'https://' . $request->SERVER['SERVER_NAME'] . $request->SERVER['REQUEST_URI'];
				
				return $response->redirect($redirect_url, $this->redirect_status);
			}

		}

		return $next($request, $response);
	}

}
