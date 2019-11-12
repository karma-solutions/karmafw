<?php

namespace KarmaFW\Routing;


class Route
{
	private $name = null;
	private $methods = [];
	private $match_url = '';
	private $match_type = 'exact';
	private $regex_params = [];
	private $callback = null;


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

	// Get route match url
	public function getMatchUrl()
	{
		return $this->match_url;
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


	// Check if route is matching the request_method and request_uri
	public function match($request_method, $request_uri)
	{
		if (empty($this->methods) || in_array($request_method, $this->methods)) {

			$request_uri_short = explode('?', $request_uri)[0];
			
			// exact match
			if ($this->match_type == 'exact') {
				if ($request_uri_short === $this->match_url) {
					return [];
				}
			}

			// startsWith
			if ($this->match_type == 'startsWith') {
				if (substr($request_uri_short, 0, strlen($this->match_url)) === $this->match_url) {
					return [];
				}
			}

			// endsWith
			if ($this->match_type == 'endsWith') {
				if (substr($request_uri_short, -1 * strlen($this->match_url)) === $this->match_url) {
					return [];
				}
			}

			// regex / regexStartsWith / regexEndsWith
			if (in_array($this->match_type, ['regex', 'regexStartsWith', 'regexEndsWith'])) {
				$match_pattern = '#^' . $this->match_url . '$#';
	
				if ($this->match_type == 'regexStartsWith') {
					$match_pattern = '#^' . $this->match_url . '#';
				}

				if ($this->match_type == 'regexEndsWith') {
					$match_pattern = '#' . $this->match_url . '$#';
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
