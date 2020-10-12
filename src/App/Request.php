<?php

namespace KarmaFW\App;


class Request
{
	protected $url = null;
	protected $method = null;
	public $GET = null;
	public $POST = null;
	public $COOKIE = null;
	public $SESSION = null;
	public $ENV = null;
	public $FILES = null;
	public $SERVER = null;


	public function __construct($url, $method)
	{
		$this->url = $url;
		$this->method = $method;
	}


	public static function createFromArgv()
	{
		// TODO
		//$request = new self($url, $method);
		//return $request;
	}


	public static function createFromGlobals()
	{
		$url = isset($_SERVER['REQUEST_URI']) ? explode("?", $_SERVER['REQUEST_URI'])[0] : null;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;

		$request = new self($url, $method);
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
