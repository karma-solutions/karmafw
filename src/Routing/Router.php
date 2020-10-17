<?php

namespace KarmaFW\Routing;

use \KarmaFW\WebApp;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class Router
{
	private static $routes = [];
	private static $routed_url = null;
	private static $config = [];


	public static function config($config)
	{
		self::$config = $config;
	}

	public static function setConfig($key, $value)
	{
		self::$config[$key] = $value;
	}
	
	public static function getConfig($key)
	{
		return self::$config[$key];
	}
	
	public static function group($config, $callable)
	{
		$old_config = self::$config;

		self::$config = $config;
		$callable();
		self::$config = $old_config;

	}

	// Register a route in the router
	public static function add($methods, $url_match, $callback=null, $type_match='exact', $regex_params=[])
	{
		$route = new Route();

		if (! empty(self::$config['prefix'])) {
			// ex: $prefix == "/fr"
			$route->setPrefix(self::$config['prefix'], 'exact', self::$config['prefix']);
		
		} else if (! empty(self::$config['prefix_regex'])) {
			// ex: $prefix == "/[a-zA-Z0-9-]+"
			$get_prefix = empty(self::$config['get_prefix']) ? null : self::$config['get_prefix'];
			$route->setPrefix(self::$config['prefix_regex'], 'regex', $get_prefix);

		} else if (! empty(self::$config['prefix_array'])) {
			// ex: $prefix == ["/fr", "/us"]
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

	public static function error404($callback=null)
	{
		return self::all('.*', $callback, 'regex');
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

	// DELETE method
	public static function delete($url_match, $callback=null, $type_match='exact', $regex_params=[])
	{
		return self::Add('DELETE', $url_match, $callback, $type_match, $regex_params);
	}

	// PUT method
	public static function put($url_match, $callback=null, $type_match='exact', $regex_params=[])
	{
		return self::Add('PUT', $url_match, $callback, $type_match, $regex_params);
	}

	// HEAD method
	public static function head($url_match, $callback=null, $type_match='exact', $regex_params=[])
	{
		return self::Add('HEAD', $url_match, $callback, $type_match, $regex_params);
	}

	// PATCH method
	public static function patch($url_match, $callback=null, $type_match='exact', $regex_params=[])
	{
		return self::Add('PATCH', $url_match, $callback, $type_match, $regex_params);
	}

	// OPTIONS method
	public static function options($url_match, $callback=null, $type_match='exact', $regex_params=[])
	{
		return self::Add('OPTIONS', $url_match, $callback, $type_match, $regex_params);
	}


	// Lookup the first matching route then execute it 
	public static function routeByUrl($request_method, $request_uri, $debug = false, $response = null)
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
					self::routeRun($route, $callback, $request_method, $request_uri, $response);

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

	public static function routeRun($route, $callback, $request_method, $request_uri, $response=null)
	{
		$matched_params = $route->getMatchedParams();

		if (gettype($callback) == 'array') {
			//echo " => ARRAY !<br />" . PHP_EOL;
			//pre($callback, 1);
			$controller = new $callback[0]($request_uri, $request_method, $route, $response);
			WebApp::$controller = $controller;
			call_user_func([$controller, $callback[1]], $matched_params);

		} else {
			//echo " => FUNCTION !<br />" . PHP_EOL;
			//pre($callback, 1);
			$callback($request_uri, $request_method, $route, $matched_params, $response);
		}


		return true;
	}


	public static function routeRequest(Request $request, Response $response)
	{
		$request_method = $request->getMethod();
		$request_uri = $request->getUrl();

		foreach (self::$routes as $route) {
			$route->setCalledMethod($request_method);
			$route->setCalledUrl($request_uri);

			$match = $route->match($request_method, $request_uri);

			if ($match) {
				$request->setRoute($route);

				$before_callback = $route->getBeforeCallback();
				if (! empty($before_callback)) {
					$before_callback($route);
				}

				$callback = $route->getCallback();
				if (empty($callback)) {
					// route found but no callback defined
					//return 0;
					return $response->setStatus(404)->setHtml('<h1>Page not Found</h1><p>Warning: route found but no callback defined</p>');

				} else if (is_callable($callback)) {
					// OK !
					self::$routed_url = $route;
					$response = self::requestRouteRun($route, $callback, $request, $response);
					return $response;

				} else {
					// route found but callback is not callable
					//return null;
					return $response->setStatus(404)->setHtml('<h1>Page not Found</h1><p>Warning: route callback is not callable</p>');
				}

			}

		}

		// no matching route
		//return false;
		return $response->setStatus(404)->setHtml('<h1>Page not Found</h1><p>Warning: no matching route</p>');
	}


	public static function requestRouteRun(Route $route, callable $callback, Request $request, Response $response)
	{
		$matched_params = $route->getMatchedParams();

		if (gettype($callback) == 'array') {
			//echo " => ARRAY !<br />" . PHP_EOL;
			//pre($callback, 1);
			$controller = new $callback[0]($request, $response);
			WebApp::$controller = $controller;

			$route_response = call_user_func([$controller, $callback[1]], $matched_params);

		} else {
			//echo " => FUNCTION !<br />" . PHP_EOL;
			//pre($callback, 1);
			$route_response = $callback($request, $response, $matched_params);
		}

		if ($route_response instanceof Response) {
			$response = $route_response;

		} else if ($route_response) {
			return $response->setHtml('<html><body><h1>Server Error</h1><p>Error: $response is not a Response</p></body></html>', 404);

		} else {
			//return $response->setHtml('<html><body><h1>Server Error</h1><p>Error: $response is empty</p></body></html>', 404);
		}

		return $response;
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
