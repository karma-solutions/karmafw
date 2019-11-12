<?php

namespace KarmaFW\Routing\Controllers;


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
	}

}
