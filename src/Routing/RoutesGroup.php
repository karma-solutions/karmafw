<?php

namespace KarmaFW\Routing;


class RoutesGroup
{
	protected $routes = [];

	public function add($route)
	{
		$this->routes[] = $route;
	}

	public function setName($name)
	{
		foreach ($this->routes as $route) {
			$route->setName($name);
		}
		return $this;
	}
	
	public function setPrefix($prefix=null, $prefix_callback=null)
	{
		foreach ($this->routes as $route) {
			$route->setPrefix($prefix, $prefix_callback);
		}
		return $this;
	}

	public function notMatch($pattern)
	{
		foreach ($this->routes as $route) {
			$route->notMatch($pattern);
		}
		return $this;
	}

}
