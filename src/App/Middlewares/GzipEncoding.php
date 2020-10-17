<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;


class GzipEncoding
{
	
	public function __invoke(Request $request, Response $response, callable $next)
	{
		$response = $next($request, $response);

		$content = (string) gzencode($response->getBody());

		if (strlen($content) > 1000) {
			$response->setBody($content);

			$response->addHeader('Content-Encoding', 'gzip');
			$response->addHeader('X-Encoding', 'gzip');
		}

		return $response;
	}

}
