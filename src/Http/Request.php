<?php

namespace KarmaFW\Http;

use \KarmaFW\Routing\Route;
use \KarmaFW\Http\UserAgent;


class Request
{
	protected $method = null;
	protected $url = null;
	protected $protocol = null;
	protected $attributes = [];

	protected $route = null;
	protected $client_ip = null;
	protected $user_agent = null;

	public $GET = null;
	public $POST = null;
	public $COOKIE = null;
	public $SESSION = null;
	public $ENV = null;
	public $FILES = null;
	public $SERVER = null;


	public function __construct($method, $url, array $headers=[], $body=null, $version='1.1')
	{
		$this->url = $url;
		$this->method = strtoupper($method);
		$this->protocol = $version;
		//$this->setHeaders($headers);

		$this->setAttribute('env', ENV);

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

		if (empty($request->SERVER['SERVER_ADDR'])) {
			$request->SERVER['SERVER_ADDR'] = '127.0.0.1';
		}

		// Set Client User-Agent
		$user_agent = isset($request->SERVER['HTTP_USER_AGENT']) ? $request->SERVER['HTTP_USER_AGENT'] : null;
		$request->setUserAgent($user_agent);

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


	public function getHost($with_scheme=false)
	{
		if ($with_scheme) {
			$scheme = $this->isSecure() ? 'https://' : 'http://';
			return $scheme . $this->SERVER['SERVER_NAME'];
			
		} else {
			return $this->SERVER['SERVER_NAME'];
		}
	}

	public function getFullUrl()
	{
		return $this->getHost(true) . $this->url;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getServerIp()
	{
		return $this->SERVER['SERVER_ADDR'];
	}

	public function getClientIp()
	{
		return $this->client_ip;
	}

	public function setClientIp($client_ip)
	{
		$this->client_ip = $client_ip;
	}

	public function getUserAgent()
	{
		return $this->user_agent;
	}

	public function setUserAgent($user_agent)
	{
		$this->user_agent = $user_agent;
	}

	public function getRoute()
	{
		return $this->route;
	}

	public function setRoute(Route $route)
	{
		$this->route = $route;
	}


	public function isGet()
	{
		return ($this->method == 'GET');
	}

	public function isPost()
	{
		return ($this->method == 'POST');
	}

	public function isHead()
	{
		return ($this->method == 'HEAD');
	}

	public function isOptions()
	{
		return ($this->method == 'OPTIONS');
	}

	public function isPut()
	{
		return ($this->method == 'PUT');
	}

	public function isDelete()
	{
		return ($this->method == 'DELETE');
	}

	public function isPatch()
	{
		return ($this->method == 'PATCH');
	}

	public function isSecure()
	{
		return (! empty($this->SERVER['HTTPS']) && $this->SERVER['HTTPS'] == 'On')
		    || (! empty($this->SERVER['REQUEST_SCHEME']) && $this->SERVER['REQUEST_SCHEME'] == 'https')
		    || (! empty($this->SERVER['HTTP_X_FORWARDED_HTTPS']) && $this->SERVER['HTTP_X_FORWARDED_HTTPS'] == 'On')
		    || (! empty($this->SERVER['HTTP_X_FORWARDED_SCHEME']) && $this->SERVER['HTTP_X_FORWARDED_SCHEME'] == 'https');
	}

	public function isBot()
	{
		return UserAgent::isBot($this->user_agent);
	}

	public function isMobile()
	{
		return UserAgent::isMobile($this->user_agent);
	}

	public function isAjax()
	{
		return (! empty($this->SERVER['HTTP_X_REQUESTED_WITH']) && $this->SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
	}



	public function getAttributes()
	{
		return $this->attributes;
	}

	public function setAttributes($attributes)
	{
		$this->attributes = $attributes;
	}

	public function getAttribute($key, $default_value=null)
	{
		return isset($this->attributes[$key]) ? $this->attributes[$key] : $default_value;
	}

	public function setAttribute($key, $value)
	{
		$this->attributes[$key] = $value;
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
