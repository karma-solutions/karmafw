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

        if ($content_type_short !== 'text/html') {
            return $response;
        }


		$content = (string) gzencode($response->getBody());

		if (strlen($content) > 1000) {
			$response->setBody($content);

			$response->addHeader('Content-Encoding', 'gzip');
			$response->addHeader('X-Encoding', 'gzip');
		}

		return $response;
	}

}
