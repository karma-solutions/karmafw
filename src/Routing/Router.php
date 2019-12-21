<?php

namespace KarmaFW\Routing;


class Router
{
	private static $routes = [];


	// Register a route in the router
	public static function add($methods, $url_match, $callback=null, $type_match='exact', $regex_params=[])
	{
		$route = new Route();

		$route->setMatchUrl($url_match);
		$route->setCallback($callback);
		$route->setMatchType($type_match	);
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

			$match_params = $route->match($request_method, $request_uri);

			if (! is_null($match_params)) {
				if ($debug) {
					echo " => MATCH !<br />" . PHP_EOL;
				}

				$callback = $route->getCallback();
				if (empty($callback)) {
					// Do nothing
					return 0;

				} else if (is_callable($callback)) {
					self::routeRun($route, $callback, $request_method, $request_uri, $match_params);

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

	public static function routeRun($route, $callback, $request_method, $request_uri, $match_params)
	{
		if (gettype($callback) == 'array') {
			$class = new $callback[0]($route, $request_method, $request_uri);
			call_user_func([$class, $callback[1]], $match_params);

		} else {
			$callback($route, $request_method, $request_uri);
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

		$link = $route->getMatchUrl();
		//echo "<pre>"; var_dump($route); exit;
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

}
