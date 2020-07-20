<?php

namespace KarmaFW\Routing;

use \KarmaFW\WebApp;


class Router
{
	private static $routes = [];
	private static $routed_url = null;
	private static $config = [];


	public static function config($config)
	{
		self::$config = $config;
	}


	// Register a route in the router
	public static function add($methods, $url_match, $callback=null, $type_match='exact', $regex_params=[])
	{
		$route = new Route();

		if (! empty(self::$config['prefix'])) {
			$route->setPrefix(self::$config['prefix'], 'exact', self::$config['prefix']);
		
		} else if (! empty(self::$config['prefix_regex'])) {
			$get_prefix = empty(self::$config['get_prefix']) ? null : self::$config['get_prefix'];
			$route->setPrefix(self::$config['prefix_regex'], 'regex', $get_prefix);

		} else if (! empty(self::$config['prefix_array'])) {
			$get_prefix = empty(self::$config['get_prefix']) ? null : self::$config['get_prefix'];
			$route->setPrefix(self::$config['prefix_array'], 'array', $get_prefix);
		}

		if (! empty(self::$config['before_callback'])) {
			$route->setBeforeCallback(self::$config['before_callback']);
		}

		$route->setMatchUrl($url_match);
		$route->setCallback($callback);
		$route->setMatchType($type_match);
		$route->setRegexParams($regex_params);
		
		if (! is_array($methods)) {
			$methods = [$methods];
		}
		foreach ($methods as $method) {
			$route->setMethod($method);
		}

		self::$routes[] = $route;

		return $route;
	}


	// Allow whatever method (GET, POST, HEAD, OPTION, DELETE, PUT, ...)
	public static function all($url_match, $callback=null, $type_match='exact', $regex_params=[])
	{
		return self::Add(null, $url_match, $callback, $type_match, $regex_params);
	}

	// GET method
	public static function get($url_match, $callback=null, $type_match='exact', $regex_params=[])
	{
		return self::Add('GET', $url_match, $callback, $type_match, $regex_params);
	}

	// POST method
	public static function post($url_match, $callback=null, $type_match='exact', $regex_params=[])
	{
		return self::Add('POST', $url_match, $callback, $type_match, $regex_params);
	}


	// Lookup the first matching route then execute it 
	public static function routeByUrl($request_method, $request_uri, $debug = false)
	{
		foreach (self::$routes as $route) {
			if ($debug) {
				pre($route);
			}

			$route->setCalledMethod($request_method);
			$route->setCalledUrl($request_uri);

			$match = $route->match($request_method, $request_uri);

			if ($match) {
				if ($debug) {
					echo " => MATCH !<br />" . PHP_EOL;
				}

				$before_callback = $route->getBeforeCallback();
				if (! empty($before_callback)) {
					$before_callback($route);
				}


				$callback = $route->getCallback();
				if (empty($callback)) {
					// Do nothing
					return 0;

				} else if (is_callable($callback)) {
					self::$routed_url = $route;
					self::routeRun($route, $callback, $request_method, $request_uri);

				} else {
					// Error: callback not callable
					return null;
				}

				return $route;
			}
		}

		// No matching route
		return false;
	}


	public static function routeRun($route, $callback, $request_method, $request_uri)
	{
		$matched_params = $route->getMatchedParams();

		if (gettype($callback) == 'array') {
			//echo " => ARRAY !<br />" . PHP_EOL;
			//pre($callback, 1);
			$controller = new $callback[0]($request_uri, $request_method, $route);
			WebApp::$controller = $controller;
			call_user_func([$controller, $callback[1]], $matched_params);

		} else {
			//echo " => FUNCTION !<br />" . PHP_EOL;
			//pre($callback, 1);
			$callback($route, $matched_params);
		}


		return true;
	}


	// Search a route by its name
	public static function findRouteByName($expected_route_name, $debug = false)
	{
		if (empty($expected_route_name)) {
			return null;
		}
		foreach (self::$routes as $route) {
			$route_name = $route->getName();
			if (! empty($route_name) && $route_name == $expected_route_name) {
				return $route;
			}
		}
		return null;
	}

	
	public static function getRouteUrl($route_name, $urls_args=[])
	{
		if (empty($urls_args)) {
			$urls_args = array();
		}

		if (! is_array($urls_args)) {
			$urls_args = array($urls_args);
		}

		$route = Router::findRouteByName($route_name);
		if (empty($route) || $route === true) {
			return null;
		}
		//pre($route, 1);


		$get_prefix = $route->getCallbackGetPrefix();
		//pre($get_prefix, 0, 'get_prefix: ');

		$link = $route->getMatchUrl();
		if ($get_prefix) {
			$link = $get_prefix . $link;
		}
		//pre($link, 1, 'link: ');


		$link = rtrim($link, '$');
		$link = ltrim($link, '^');

		$link = str_replace('\\.', '.', $link);
		$link = str_replace('\\?', '?', $link);
		$link = str_replace('\\+', '+', $link);
		$link = str_replace('\\-', '-', $link);

		if (! empty($urls_args)) {
			foreach ($urls_args as $arg_value) {
				$pos1 = strpos($link, '(');
				if ($pos1 === false) {
					break;
				}
				$pos2 = strpos($link, ')', $pos1);
				if ($pos2 === false) {
					break;
				}
				$link = substr($link, 0, $pos1) . $arg_value . substr($link, $pos2+1);
			}
		}

		return $link;
	}


	public static function printRoutes()
	{
		dump(self::$routes);
		exit;
	}


	public static function getRoutedUrl()
	{
		return self::$routed_url;
	}

}
