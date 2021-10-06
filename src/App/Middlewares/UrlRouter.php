<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;
//use \KarmaFW\Routing\Router;


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
		if (is_file(FW_DIR . '/config/routes.php')) {
			require FW_DIR . '/config/routes.php';
		}
		if (is_file(APP_DIR . '/config/routes.php')) {
			require APP_DIR . '/config/routes.php';
		}


		try {
			ob_start();
			
			//$route_response = Router::routeRequest($request, $response);
			$app = App::getData('app');
			$router = $app->get('router');
			$route_response = $router($request, $response);

			// en principe le contenu de la reponse est dans $response->body
			// mais si il y a eu des "echo", ils sont capturés par le ob_start puis insérés au début de $response->body

			$content = ob_get_contents();
			ob_end_clean();

			if (! empty($route_response) && is_a($route_response, Response::class)) {
				$response = $route_response;
			}

			$response->prepend($content); // on ajoute ici le texte capturé pendant l'execution de la route

			$content_type = $response->getContentType();
			if (empty($content_type)) {
				$content_type = 'text/html; charset=utf-8';
				$response->setContentType($content_type);
			}

			$response->addHeader('X-Content-Type', $content_type);

			$response = $next($request, $response);

		} catch (\Exception $e) {
			$error_code = $e->getCode();
			$error_message = $e->getMessage();


			// TODO: voir comment bien injecter cette dependance
			$debugbar = App::getData('debugbar');
			if ($debugbar) {
	            if (isset($debugbar['exceptions'])) {
					$debugbar['exceptions']->addException($e);
	            }
	        }


	        // CODE 200
			if ($error_code === 200) {
				return $response->html($error_message, $error_code);
			}


			// REDIRECTION
			if (in_array($error_code, [301, 302, 310])) {
				// if $error_code is a redirection
				$url = $error_message;
				//return new ResponseRedirect($url, $error_code);
				return $response->redirect($url, $error_code);
			}

			// ERROR 404
			if (in_array($error_code, [404, 410])) {
				// if $error_code is a 404 page not found
				if (empty($error_message)) {
					$error_message = '<title>Not Found</title><h1>Not Found</h1><p>Page not Found</p>';
				}
				return $response->html($error_message, $error_code);
			}

			// ERROR 403
			if (in_array($error_code, [401, 403])) {
				// if $error_code is a 403 page not found
				if (empty($error_message)) {
					$error_message = '<title>Access denied</title><h1>Access denied</h1><p>Page access denied</p>';
				}
				return $response->html($error_message, $error_code);
			}

			// ERROR 400
			if (in_array($error_code, [400])) {
				// if $error_code is a 400 page not found
				if (empty($error_message)) {
					$error_message = '<title>Bad request</title><h1>Bad request</h1><p>Bad request</p>';
				}
				return $response->html($error_message, $error_code);
			}


			// ERROR 500
			if (in_array($error_code, [500])) {
				// if $error_code is a 500 page not found
				if (empty($error_message)) {
					$error_message = '<title>Server error</title><h1>Server error</h1><p>An error has occured</p>';
				}
				return $response->html($error_message, $error_code);
			}


			if (! $this->catch_exceptions) {
				// on relance l'exception => pour laisser la gestion de l'erreur à un handler parent (ou le error_handler par defaut de PHP)
				throw $e;
			}

			error_log("[UrlRouter Error 500] : " . $error_message);


            if (ENV == 'dev') {
                $title = "UrlRouter CATCHED EXCEPTION CODE " . $error_code;
                $message = '<pre>' . print_r($e, true) . '</pre>';
                $response_content = '<title>' . $title . '</title><h1>' . $title . '</h1><h2>' . $error_message . '</h2><p>' . $message . '</p>';

            } else {
                $title = "Server Error";
                $message = 'An error has occured';
                $response_content = '<title>' . $title . '</title><h1>' . $title . '</h1><p>' . $message . '</p>';
            }

			if (App::isCli()) {
				echo $title . PHP_EOL . PHP_EOL;
				echo $message . PHP_EOL . PHP_EOL;
				exit;
			}


			// else => error 500
			$response->html($response_content, 500);
		}

		return $response;
	}

}
