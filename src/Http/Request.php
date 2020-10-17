<?php

namespace KarmaFW\Http;

use \KarmaFW\Routing\Route;

// TODO: a remplacer par ou rendre compatible avec GuzzleHttp\Psr7\Request

class Request
{
	protected $method = null;
	protected $url = null;
	protected $protocol = null;

	protected $client_ip = null;
	protected $route = null;

	public $GET = null;
	public $POST = null;
	public $COOKIE = null;
	public $SESSION = null;
	public $ENV = null;
	public $FILES = null;
	public $SERVER = null;


	//public function __construct($url=null, $method=null)
	public function __construct($method, $url, array $headers=[], $body=null, $version='1.1')
	{
		$this->url = $url;
		$this->method = strtoupper($method);
		$this->protocol = $version;
		//$this->setHeaders($headers);

		//print_r($_SERVER); exit;
	}


	public static function createFromArgv()
	{
		// TODO
		$request = new self(null, null);


		$request->GET = isset($_GET) ? $_GET : [];
		$request->POST = isset($_POST) ? $_POST : [];
		$request->COOKIE = isset($_COOKIE) ? $_COOKIE : [];
		$request->SESSION = isset($_SESSION) ? $_SESSION : [];
		$request->ENV = isset($_ENV) ? $_ENV : [];
		$request->FILES = isset($_FILES) ? $_FILES : [];
		$request->SERVER = isset($_SERVER) ? $_SERVER : [];

		$client_ip = null;
		$request->setClientIp($client_ip);
		
		return $request;
	}


	public static function createFromGlobals()
	{
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
		
		//$url = isset($_SERVER['REQUEST_URI']) ? explode("?", $_SERVER['REQUEST_URI'])[0] : null;
		$url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;


		$request = new self($method, $url);

		/*
		$request->setGet(isset($_GET) ? $_GET : []);
		$request->setPost(isset($_POST) ? $_POST : []);
		$request->setCookie(isset($_COOKIE) ? $_COOKIE : []);
		$request->setSession(isset($_SESSION) ? $_SESSION : []);
		$request->setEnv(isset($_ENV) ? $_ENV : []);
		$request->setFiles(isset($_FILES) ? $_FILES : []);
		$request->setServer(isset($_SERVER) ? $_SERVER : []);
		*/

		$request->GET = isset($_GET) ? $_GET : [];
		$request->POST = isset($_POST) ? $_POST : [];
		$request->COOKIE = isset($_COOKIE) ? $_COOKIE : [];
		$request->SESSION = isset($_SESSION) ? $_SESSION : [];
		$request->ENV = isset($_ENV) ? $_ENV : [];
		$request->FILES = isset($_FILES) ? $_FILES : [];
		$request->SERVER = isset($_SERVER) ? $_SERVER : [];


		// Set Server name (if behind a proxy)
		if (! empty($request->SERVER['HTTP_X_FORWARDED_HOST'])) {
			// if "ProxyPreserveHost On" is not set in apache
			$request->SERVER['HTTP_HOST']   = $request->SERVER['HTTP_X_FORWARDED_HOST'];
			$request->SERVER['SERVER_NAME'] = $request->SERVER['HTTP_X_FORWARDED_HOST'];
		}

		// Set Client IP
		$client_ip = null;
		if (! empty($request->SERVER['REMOTE_ADDR'])) {
			$client_ip = $request->SERVER['REMOTE_ADDR'];
		}
		if (! empty($request->SERVER['HTTP_X_FORWARDED_FOR'])) {
			$client_ip = $request->SERVER['HTTP_X_FORWARDED_FOR'];
		}
		$request->setClientIp($client_ip);

		return $request;
	}


	public function getUrl()
	{
		return $this->url;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getClientIp()
	{
		return $this->client_ip;
	}

	public function setClientIp($client_ip)
	{
		$this->client_ip = $client_ip;
	}

	public function getRoute()
	{
		return $this->route;
	}

	public function setRoute(Route $route)
	{
		$this->route = $route;
	}

	public function isSecure()
	{
		return (! empty($this->SERVER['HTTPS']) && $this->SERVER['HTTPS'] == 'On')
		    || (! empty($this->SERVER['REQUEST_SCHEME']) && $this->SERVER['REQUEST_SCHEME'] == 'https')
		    || (! empty($this->SERVER['HTTP_X_FORWARDED_HTTPS']) && $this->SERVER['HTTP_X_FORWARDED_HTTPS'] == 'On')
		    || (! empty($this->SERVER['HTTP_X_FORWARDED_SCHEME']) && $this->SERVER['HTTP_X_FORWARDED_SCHEME'] == 'https');
	}

	/*

	public function setUrl($url)
	{
		$this->url = $url;
	}


	public function setMethod($method)
	{
		$this->method = $method;
	}

	public function getGet()
	{
		return is_null($key) ? $this->GET : (isset($this->GET[$key]) ? $this->GET[$key] : null);
	}

	public function setGet($GET)
	{
		$this->GET = $GET;
	}

	public function getPost()
	{
		return is_null($key) ? $this->POST : (isset($this->POST[$key]) ? $this->POST[$key] : null);
	}

	public function setPost($POST)
	{
		$this->POST = $POST;
	}

	public function getCookie()
	{
		return is_null($key) ? $this->COOKIE : (isset($this->COOKIE[$key]) ? $this->COOKIE[$key] : null);
	}

	public function setCookie($COOKIE)
	{
		$this->COOKIE = $COOKIE;
	}

	public function getSession()
	{
		return is_null($key) ? $this->SESSION : (isset($this->SESSION[$key]) ? $this->SESSION[$key] : null);
	}

	public function setSession($SESSION)
	{
		$this->SESSION = $SESSION;
	}

	public function getEnv()
	{
		return is_null($key) ? $this->ENV : (isset($this->ENV[$key]) ? $this->ENV[$key] : null);
	}

	public function setEnv($ENV)
	{
		$this->ENV = $ENV;
	}

	public function getFiles()
	{
		return is_null($key) ? $this->FILES : (isset($this->FILES[$key]) ? $this->FILES[$key] : null);
	}

	public function setFiles($FILES)
	{
		$this->FILES = $FILES;
	}

	public function getServer($key=null)
	{
		return is_null($key) ? $this->SERVER : (isset($this->SERVER[$key]) ? $this->SERVER[$key] : null);
	}

	public function setServer($SERVER)
	{
		$this->SERVER = $SERVER;
	}
	*/


}