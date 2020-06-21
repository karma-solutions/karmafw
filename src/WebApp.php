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

		// start session
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


	public static function route()
	{
		if (! self::$booted) {
			self::boot();
		}

		// routing: parse l'url puis transfert au controller

		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('app.route.before', []);
		}

		$route = Router::routeByUrl( $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], false );

		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('app.route.after', [$route]);
		}

		if ($route) {
			//echo "success: route ok";
			if (defined('USE_HOOKS') && USE_HOOKS) {
				HooksManager::applyHook('app.route.success', [$_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $route]);
			}
			exit(0);

		} else if ($route === null) {
			// route found but callback is not callable
			self::error404('Warning: route callback is not callable', '404 Not Found');
			exit(1);

		} else if ($route === 0) {
			// route found but no callback defined
			if (defined('USE_HOOKS') && USE_HOOKS) {
				HooksManager::applyHook('app.route.error', []);
			}
			self::error404("Warning: route found but no callback defined", '404 Not Found');
			exit(1);

		} else if ($route === false) {
			// no matching route
			if (defined('USE_HOOKS') && USE_HOOKS) {
				HooksManager::applyHook('app.route.error', []);
			}
			self::error404("Warning: no matching route", '404 Not Found');
			exit(1);

		} else {
			// other cases
			if (defined('USE_HOOKS') && USE_HOOKS) {
				HooksManager::applyHook('app.route.error', []);
			}
			self::error404("Warning: cannot route", '404 Not Found');
			exit(1);
		}

	}


	public static function error($http_status = 500, $meta_title = 'Server Error', $h1 = 'Error 500 - Server Error', $content = 'an error has occured', $error_template = 'error.tpl.php')
	{
		if (! self::$controller) {
			self::$controller = new WebAppController();
		}
		if (self::$controller && $template = self::$controller->getTemplate()) {
			$template->assign('meta_title', $meta_title);
			$template->assign('h1', $h1);
			$template->assign('p', $content);
			$template->assign('http_status', $http_status);

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
			if (! empty($content)) {
				$output_html .= '<p>' . $content . '</p>' . PHP_EOL;
			}
			$output_html .= '</body>' . PHP_EOL;
			$output_html .= '</html>' . PHP_EOL;

			echo $output_html;
		}

		exit;
	}


	public static function error400($title = 'Bad request')
	{
		$meta_title = $title;
		$h1 = $title;
		$content = '';
		return self::error(400, $meta_title, $h1, $content);
	}

	public static function error403($title = 'Forbidden')
	{
		$meta_title = $title;
		$h1 = $title;
		$content = 'You are not allowed';
		return self::error(403, $meta_title, $h1, $content);
	}

	public static function error404($message = "The page you're looking for doesn't exist.", $title = 'Page not Found')
	{
		return self::error(404, $title, $title, $message);
	}

	public static function error500($title = 'Internal Server Error')
	{
		$meta_title = $title;
		$h1 = $title;
		$content = 'An error has occured';
		return $this->error(500, $meta_title, $h1, $content);
	}

	public static function error503($title = 'Service Unavailable')
	{
		$meta_title = $title;
		$h1 = $title;
		$content = 'The service is unavailable';
		return $this->error(503, $meta_title, $h1, $content);
	}

}
