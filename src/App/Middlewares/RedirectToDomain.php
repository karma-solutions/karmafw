<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;
use \KarmaFW\App\ResponseRedirect;


class RedirectToDomain
{
	protected $target_domain = 'example.com';
	protected $redirect_domains = []; // www.example.com, example.fr, www.example.fr


	public function __construct($target_domain, $redirect_domains=[])
	{
		$this->target_domain = strtolower($target_domain);
		$this->redirect_domains = $redirect_domains;
	}


	public function __invoke(Request $request, Response $response, callable $next)
	{
		if (strtolower($request->SERVER['SERVER_NAME']) != $this->target_domain) {

			if (empty($this->redirect_domains) || in_array($this->target_domain, $this->redirect_domains)) {
				$redirect_url = 'https://' . $this->target_domain . $request->SERVER['REQUEST_URI'];
				return new ResponseRedirect($redirect_url, $this->redirect_status);
			}

		}

		return $next($request, $response);
	}

}
