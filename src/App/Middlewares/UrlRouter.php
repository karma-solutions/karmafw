<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;
use \KarmaFW\App\ResponseError404;
use \KarmaFW\App\ResponseRedirect;
use \KarmaFW\App\ResponseFile;
use \KarmaFW\Routing\Router;


class UrlRouter
{
	
	public function __invoke(Request $request, Response $response, callable $next)
	{
		// LOAD ROUTES
		if (is_file(APP_DIR . '/config/routes.php')) {
			require APP_DIR . '/config/routes.php';
		}


		try {
			$router = new Router;

			ob_start();
			
			$response = Router::routeRequest($request, $response);

			// en principe le contenu de la reponse est dans $response->content
			// mais si il y a eu des "echo", ils sont capturés par le ob_start puis insérés au début de $response->content

			$content = ob_get_contents();
			ob_end_clean();
			$response->prepend($content);

			$response = $next($request, $response);

		} catch (\Throwable $e) {
			echo "UrlRouter CATCHED EXCEPTION" . PHP_EOL; // TODO
			print_r($e);
		}

		return $response;
	}

}