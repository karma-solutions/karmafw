<?php

namespace KarmaFW;

use \KarmaFW\Routing\Router;
use \KarmaFW\Routing\Controllers\WebAppController;
use \KarmaFW\Lib\Hooks\HooksManager;
use \KarmaFW\Templates\PhpTemplate;


class WebApp extends App
{
	public static $session_user = false; // user connected with a session
	public static $controller = null;


	public static function boot()
	{
		parent::boot();

		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('webapp.boot.before', []);
		}


		// START SESSION
		if (empty(session_id())) {
			if (defined('SESSION_NAME') && ! empty(SESSION_NAME)) {
				session_name(SESSION_NAME);
			}

			if (defined('SESSION_DURATION') && is_numeric(SESSION_DURATION)) {
				ini_set('session.gc_maxlifetime', SESSION_DURATION);
				session_set_cookie_params(SESSION_DURATION);
				// Note: si cron est actif, il faut modifier la valeur de session.gc_maxlifetime dans /etc/php/7.3/apache2/php.ini (voir /etc/cron.d/php)
			}

			session_start();
		}


		// LOAD ROUTES
		require APP_DIR . '/config/routes.php'; // NOTE => a déplacer dans \KarmaFW\WebApp::boot() ??


		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('webapp.boot.after', []);
		}
	}


	public static function createTemplate($tpl_path=null, $variables=[], $layout=null, $templates_dirs=null)
	{
		return new PhpTemplate($tpl_path, $variables, $layout, $templates_dirs);
	}


	/*
	public static function getUser()
	{
		return self::$session_user;
	}

	public static function setUser($user)
	{
		self::$session_user = $user;
	}
	*/


	// deprecated
	public static function route()
	{
		return self::routeUrl();
	}


	public static function routeUrl()
	{
		if (! self::$booted) {
			self::boot();
		}

		// routing: parse l'url puis transfert au controller

		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('webapp.route.before', []);
		}

		$route = Router::routeByUrl( $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], false );

		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('webapp.route.after', [$route]);
		}

		if ($route) {
			//echo "success: route ok";
			if (defined('USE_HOOKS') && USE_HOOKS) {
				HooksManager::applyHook('webapp.route.success', [$_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $route]);
			}
			exit(0);

		} else if ($route === null) {
			// route found but callback is not callable
			self::error404('Page not Found', 'Warning: route callback is not callable');
			exit(1);

		} else if ($route === 0) {
			// route found but no callback defined
			if (defined('USE_HOOKS') && USE_HOOKS) {
				HooksManager::applyHook('webapp.route.error', []);
			}
			self::error404('Page not Found', "Warning: route found but no callback defined");
			exit(1);

		} else if ($route === false) {
			// no matching route
			if (defined('USE_HOOKS') && USE_HOOKS) {
				HooksManager::applyHook('webapp.route.error', []);
			}
			self::error404('Page not Found', "Warning: no matching route");
			exit(1);

		} else {
			// other cases
			if (defined('USE_HOOKS') && USE_HOOKS) {
				HooksManager::applyHook('webapp.route.error', []);
			}
			self::error404('Page not Found', "Warning: cannot route");
			exit(1);
		}

	}


	public static function error($http_status = 500, $meta_title = 'Server Error', $h1 = 'Error 500 - Server Error', $message = 'an error has occured')
	{
		if (! self::$controller) {
			self::$controller = new WebAppController();
		}
		if (self::$controller && $template = self::$controller->getTemplate()) {
			$template->assign('meta_title', $meta_title);
			$template->assign('h1', $h1);
			$template->assign('p', $message);
			$template->assign('http_status', $http_status);

			$error_template = 'error.tpl.php';
			if (defined('ERROR_TEMPLATE')) {
				$error_template = ERROR_TEMPLATE;
			}

			$template->display($error_template);

		} else {
			//header("HTTP/1.0 " . $error_code . " " . $title);

			$output_html = '';
			$output_html .= '<html>' . PHP_EOL;
			$output_html .= '<head>' . PHP_EOL;
			if (! empty($meta_title)) {
				$output_html .= '<title>' . $meta_title . '</title>' . PHP_EOL;
			}
			$output_html .= '</head>' . PHP_EOL;
			$output_html .= '<body>' . PHP_EOL;
			if (! empty($h1)) {
				$output_html .= '<h1>' . $h1 . '</h1>' . PHP_EOL;
			}
			if (! empty($message)) {
				$output_html .= '<p>' . $message . '</p>' . PHP_EOL;
			}
			$output_html .= '</body>' . PHP_EOL;
			$output_html .= '</html>' . PHP_EOL;

			echo $output_html;
		}

		exit;
	}


	public static function error400($title = 'Bad request', $message = '')
	{
		return self::error(400, $title, $title, $message);
	}

	public static function error403($title = 'Forbidden', $message = 'You are not allowed')
	{
		return self::error(403, $title, $title, $message);
	}

	public static function error404($title = 'Page not Found', $message = "The page you're looking for doesn't exist")
	{
		return self::error(404, $title, $title, $message);
	}

	public static function error500($title = 'Internal Server Error', $message = 'An error has occured')
	{
		return $this->error(500, $title, $title, $message);
	}

	public static function error503($title = 'Service Unavailable', $message = 'The service is unavailable')
	{
		return $this->error(503, $title, $title, $message);
	}

}
