<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;
use \KarmaFW\Routing\Router;


class UrlRouter
{
	protected $catch_exceptions;


	public function __construct($catch_exceptions=false)
	{
		$this->catch_exceptions = $catch_exceptions;
	}


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
			$error_code = $e->getCode();
			$error_message = $e->getMessage();

			/*
			$is_response = is_a($e, Response::class);
			if ($is_response) {
				// exception is in reality a Response
				return $e;
			}
			*/

			if (in_array($error_code, [301, 302, 310])) {
				// if $error_code is a redirection
				$url = $error_message;
				//return new ResponseRedirect($url, $error_code);
				return $response->redirect($url, $error_code);
			}

			// ERROR 404
			if ($error_code == 404) {
				// if $error_code is a 404 page not found
				if (empty($error_message)) {
					$error_message = '<title>Not Found</title><h1>Not Found</h1>';
				}
				return $response->setStatus(404)->setHtml($error_message);
			}


			// ERROR 500

			if (! $this->catch_exceptions) {
				// on relance l'exception => pour laisser la gestion de l'erreur à un handler parent (ou le error_handler par defaut de PHP)
				throw $e;
			}

			error_log("[UrlRouter] Error 500 : " . $error_message);


            if (ENV == 'dev') {
                $title = "UrlRouter CATCHED EXCEPTION CODE " . $error_code;
                $message = '<pre>' . print_r($e, true) . '</pre>';
                $response_content = '<title>' . $title . '</title><h1>' . $title . '</h1><h2>' . $error_message . '</h2><p>' . $message . '</p>';

            } else {
                $title = "Server Error";
                $message = 'An error has occured';
                $response_content = '<title>' . $title . '</title><h1>' . $title . '</h1><p>' . $message . '</p>';
            }


			// else => error 500
			$response->setStatus(500)->setHtml($response_content);
		}

		return $response;
	}

}
