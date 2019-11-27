<?php

namespace KarmaFW\Routing\Controllers;

use \KarmaFW\Lib\Hooks\HooksManager;


class WebController
{
	protected $route = null;
	protected $request_method = null;
	protected $request_uri = null;


	public function __construct($route, $request_method, $request_uri)
	{
		$this->route = $route;
		$this->request_method = $request_method;
		$this->request_uri = $request_uri;
		
		//echo "DEBUG " . __CLASS__ . ": controller instanced<hr />" . PHP_EOL;

		HooksManager::applyHook('webcontroller__init', [$this]);
	}


	public function getRoute()
	{
		return $this->route;
	}

	public function getRequestMethod()
	{
		return $this->request_method;
	}

	public function getRequestUri()
	{
		return $this->request_uri;
	}

}
