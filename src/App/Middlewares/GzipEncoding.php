<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class GzipEncoding
{
	
	public function __invoke(Request $request, Response $response, callable $next)
	{
		$response = $next($request, $response);


		$content_type = $response->getContentType();
		$content_type_short = explode(';', $content_type)[0];

		$contents_types = [
			'text/html',
			'text/plain',
			'text/xml',
			'text/css',
			'application/x-javascript',
			'application/javascript',
			'application/ecmascript',
			'application/json',
			'application/xml',
			'image/svg+xml',
		];

		if (empty($content_types) || ! in_array($content_type_short, $content_types)) {
			return $response;
		}

		$content_length = $response->getContentLength();


		if ($content_length > 1000) {
			$content_minimified = (string) gzencode($response->getBody());
			$response->setBody($content_minimified);
			$content_minimified_length = $response->getContentLength();


			$response->addHeader('Content-Encoding', 'gzip');
			$response->addHeader('X-Encoding', 'gzip');

			// add information headers
			$response->addHeader('X-Before-Encoding-Content-Length', $content_length);
			$response->addHeader('X-After-Encoding-Content-Length', $content_minimified_length);
		}

		return $response;
	}

}
