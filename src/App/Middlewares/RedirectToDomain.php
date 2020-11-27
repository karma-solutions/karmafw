<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


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
		$current_domain = $request->SERVER['SERVER_NAME'];

		if (strtolower($current_domain) != strtolower($this->target_domain)) {

			if (empty($this->redirect_domains) || in_array($current_domain, $this->redirect_domains)) {
				$redirect_url = 'https://' . $this->target_domain . $request->SERVER['REQUEST_URI'];
				
				return $response->redirect($redirect_url, $this->redirect_status);
			}

		}

		return $next($request, $response);
	}

}
