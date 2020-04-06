<?php

namespace KarmaFW\Routing;


class Router
{
	private static $routes = [];
	private static $prefixes = [];
	private static $routed_url = null;


	// Register a route in the router
	public static function add($methods, $url_match, $callback=null, $type_match='exact', $regex_params=[])
	{
		$prefixes = self::$prefixes ?: ['' => null];

		$routes_group = new RoutesGroup;

		foreach ($prefixes as $prefix => $prefix_callback) {
			$route = new Route();

			$route->setPrefix($prefix, $prefix_callback);
			//$route->setPrefixCallback($prefix_callback);
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

			$routes_group->add($route);
			self::$routes[] = $route;
		}


		return $routes_group;
		//return $route;
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

			$match_params = $route->match($request_method, $request_uri);

			if (! is_null($match_params)) {
				if ($debug) {
					echo " => MATCH !<br />" . PHP_EOL;
				}
				
				$prefix_callback = $route->getPrefixCallback();
				if (! empty($prefix_callback) && is_callable($prefix_callback)) {
					$prefix_callback();
				}

				$callback = $route->getCallback();
				if (empty($callback)) {
					// Do nothing
					return 0;

				} else if (is_callable($callback)) {
					self::$routed_url = $route;
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
		$route->setMatchedParams($match_params);

		if (gettype($callback) == 'array') {
			//echo " => ARRAY !<br />" . PHP_EOL;
			//pre($callback, 1);
			$class = new $callback[0]($route, $request_method, $request_uri);
			call_user_func([$class, $callback[1]], $match_params);

		} else {
			//echo " => FUNCTION !<br />" . PHP_EOL;
			//pre($callback, 1);
			//$callback($route, $request_method, $request_uri);
			$callback($route, $match_params);
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


		$prefix = self::$routed_url ? self::$routed_url->getPrefix() : '';
		if ($prefix) {
			$route_prefix = $route->getPrefix();
			$route->setPrefix($prefix);
		}

		$link = $route->getMatchUrl();

		if ($prefix) {
			$route->setPrefix($route_prefix);
		}


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
		dump(self::$prefixes);
		dump(self::$routes);
		exit;
	}


	public static function prefix($prefix, $callback)
	{
		self::$prefixes[$prefix] = $callback;
	}


	public static function clearPrefixes()
	{
		self::$prefixes = [];
	}


	public static function getRoutedUrl()
	{
		return self::$routed_url;
	}

}
