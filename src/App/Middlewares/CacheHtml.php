<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;


class CacheHtml
{
	protected $cache_duration;
	protected $cache_dir;

    
    public function __construct($cache_dir='/tmp', $cache_duration=3600)
    {
    	$this->cache_duration = $cache_duration;
    	$this->cache_dir = $cache_dir;
    }


    public function __invoke(Request $request, Response $response, callable $next)
    {
    	$request_uri = $request->SERVER['REQUEST_URI'];
    	
    	$cache_key = md5($request_uri);
    	$cache_file = $this->cache_dir . '/' . $cache_key . '.cache.html';

    	if (is_file($cache_file) && filectime($cache_file) > time() - $this->cache_duration ) {
    		// Get response content from file cache
    		$content = file_get_contents($cache_file);
    		$response->setContent($content);
        	$response->addHeader('X-Cache-Html', 'hit');

    	} else {
        	$response = $next($request, $response);

        	file_put_contents($cache_file, $response->getContent());
        	$response->addHeader('X-Cache-Html', 'miss');
    	}

        return $response;
    }

}
