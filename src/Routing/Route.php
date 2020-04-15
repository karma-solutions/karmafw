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
	private $before_callback = null;
	private $callback = null;
	private $prefix = '';
	private $prefix_match_type = null;
	private $get_prefix = null;
	private $matched_prefix = '';
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
		//return $this->prefix . $this->match_url;
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


	// Get route before_callback
	public function setBeforeCallback($before_callback)
	{
		$this->before_callback = $before_callback;
	}

	// Get route before_callback
	public function getBeforeCallback()
	{
		return $this->before_callback;
	}


	// Set route prefix
	public function setPrefix($prefix=null, $match_type=null, $get_prefix=null)
	{
		$this->prefix = $prefix;
		$this->prefix_match_type = $match_type;
		$this->get_prefix = $get_prefix;
	}

	// Get route prefix
	public function getPrefix()
	{
		return $this->prefix;
	}


	// Get route get_prefix
	public function getCallbackGetPrefix()
	{
		if (empty($this->get_prefix)) {
			return null;
		}

		$func = $this->get_prefix;
		if (! is_callable($func)) {
			//error500("get_prefix is not callable");
			return null;
		}
		//pre($func, 1);
		return $func();
	}


	// Check if route is matching the request_method and request_uri
	public function match($request_method, $request_uri)
	{
		if (empty($this->methods) || in_array($request_method, $this->methods)) {

			$request_uri_short = explode('?', $request_uri)[0];

			$matched_params = [];
			
			$prefix = $this->getPrefix();
			if ($prefix) {
				if ($this->prefix_match_type == 'regex') {
					$match_pattern = '#^(' . $prefix . ')#';
					if (preg_match($match_pattern, $request_uri_short, $regs)) {
						$this->matched_prefix = $regs[1];
						$matched_params['prefix'] = $this->matched_prefix;
						//pre($matched_prefix, 1, 'regex matched_prefix: ');
					}

				} else if ($this->prefix_match_type == 'array') {
					foreach ($prefix as $prefix_value) {
						if (strpos($request_uri_short, $prefix_value) === 0) {
							$this->matched_prefix = $prefix_value;
							$matched_params['prefix'] = $this->matched_prefix;
							//pre($matched_prefix, 1, 'array matched_prefix: ');
						}
					}

				} else {
					if (strpos($request_uri_short, $prefix) === 0) {
						$this->matched_prefix = $prefix;
						$matched_params['prefix'] = $this->matched_prefix;
						//pre($matched_prefix, 1, 'exact matched_prefix: ');
					}

				}
			}


			if (! empty($this->matched_prefix)) {
				$request_uri_short = substr($request_uri_short, strlen($this->matched_prefix));
			}
			

			$match_url = $this->getMatchUrl();
			
			// exact match
			if ($this->match_type == 'exact') {
				if ($request_uri_short === $match_url) {
					$this->setMatchedParams($matched_params);
					return true;
				}
			}

			// startsWith
			if ($this->match_type == 'startsWith') {
				if (substr($request_uri_short, 0, strlen($match_url)) === $match_url) {
					$this->setMatchedParams($matched_params);
					return true;
				}
			}

			// endsWith
			if ($this->match_type == 'endsWith') {
				if (substr($request_uri_short, -1 * strlen($match_url)) === $match_url) {
					$this->setMatchedParams($matched_params);
					return true;
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
					//pre($args, 1);

					$matched_params = array_merge($matched_params, $args);
					//pre($matched_params, 1);

					$this->setMatchedParams($matched_params);
					return true;
				}
			}

		}

		return null;
	}

}
