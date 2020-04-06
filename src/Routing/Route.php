<?php

namespace KarmaFW\Routing;


class Route
{
	private $name = null;
	private $methods = [];
	private $called_method = null;
	private $called_url = null;
	private $match_url = '';
	private $match_type = 'exact';
	private $regex_params = [];
	private $callback = null;
	private $prefix = '';
	private $prefix_callback = null;
	private $nomatch_patterns = [];
	private $matched_params = [];


	public function __construct()
	{

	}

	// Set route method (can be called multiple times for differents methods)
	public function setMethod($method = null)
	{
		if (! is_null($method)) {
			$this->methods[] = $method;
		}
	}

	// Set route match url
	public function setMatchUrl($match_url)
	{
		$this->match_url = $match_url;
	}

	// Set route called method
	public function setCalledMethod($called_method)
	{
		$this->called_method = $called_method;
	}

	// Set route called url
	public function setCalledUrl($called_url)
	{
		$this->called_url = $called_url;
	}

	// Get route match url
	public function getMatchUrl()
	{
		return $this->prefix . $this->match_url;
	}

	// Set route match type (exact, startsWith, endsWith, regex, regexStartsWith, regexEndsWith)
	public function setMatchType($match_type)
	{
		$this->match_type = $match_type;
	}

	// Set route regex params (WORKS WITH: regex, regexStartsWith, regexEndsWith)
	public function setRegexParams(array $regex_params)
	{
		$this->regex_params = $regex_params;
	}

	public function getRegexParams()
	{
		return $this->regex_params;
	}

	public function setMatchedParams(array $matched_params)
	{
		$this->matched_params = $matched_params;
	}
	public function getMatchedParams()
	{
		return $this->matched_params;
	}

	// Get route name
	public function getName()
	{
		return $this->name;
	}

	// Set route name
	public function setName($name)
	{
		$this->name = $name;
	}

	// Set route callback
	public function setCallback($callback)
	{
		$this->callback = $callback;
	}

	// Get route callback
	public function getCallback()
	{
		return $this->callback;
	}


	// Set route prefix
	public function setPrefix($prefix=null, $callback=null)
	{
		$this->prefix = $prefix;
		$this->prefix_callback = $callback;
	}

	// Get route prefix
	public function getPrefix()
	{
		return $this->prefix;
	}

	// Set route prefix callback
	public function setPrefixCallback($callback)
	{
		$this->prefix_callback = $callback;
	}

	// Get route prefix callback
	public function getPrefixCallback()
	{
		return $this->prefix_callback;
	}


	// Declare pattern to not match
	public function notMatch($pattern)
	{
		if (! is_array($pattern)) {
			$pattern = [$pattern];
		}
		foreach ($pattern as $p) {
			$this->nomatch_patterns[] = $p;
		}
	}



	// Check if route is matching the request_method and request_uri
	public function match($request_method, $request_uri)
	{
		if (empty($this->methods) || in_array($request_method, $this->methods)) {

			$request_uri_short = explode('?', $request_uri)[0];

			// on verifie qu'il n'y a pas un pattern de nomatching
			if ($this->nomatch_patterns) {
				foreach ($this->nomatch_patterns as $pattern) {
					if (preg_match('#' . $pattern . '#', $request_uri_short, $regs)) {
						return null;
					}
				}
			}

			$match_url = $this->getMatchUrl();
			
			// exact match
			if ($this->match_type == 'exact') {
				if ($request_uri_short === $match_url) {
					return [];
				}
			}

			// startsWith
			if ($this->match_type == 'startsWith') {
				if (substr($request_uri_short, 0, strlen($match_url)) === $match_url) {
					return [];
				}
			}

			// endsWith
			if ($this->match_type == 'endsWith') {
				if (substr($request_uri_short, -1 * strlen($match_url)) === $match_url) {
					return [];
				}
			}

			// regex / regexStartsWith / regexEndsWith
			if (in_array($this->match_type, ['regex', 'regexStartsWith', 'regexEndsWith'])) {
				$match_pattern = '#^' . $match_url . '$#';
	
				if ($this->match_type == 'regexStartsWith') {
					$match_pattern = '#^' . $match_url . '#';
				}

				if ($this->match_type == 'regexEndsWith') {
					$match_pattern = '#' . $match_url . '$#';
				}

				if (preg_match($match_pattern, $request_uri_short, $regs)) {
					$matched_uri = array_shift($regs); // $matched_uri == $request_uri_short
					$args = $regs;

					if (! empty($this->regex_params)) {
						$args = array_combine($this->regex_params, $args);
					}

					return $args;
				}
			}

		}

		return null;
	}

}
