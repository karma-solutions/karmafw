<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


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

        $cacheable = $request->isGet();
    	
    	$cache_key = md5($request_uri);
    	$cache_file = $this->cache_dir . '/' . $cache_key . '.cache.html';

    	if ($cacheable && is_file($cache_file) && filectime($cache_file) > time() - $this->cache_duration ) {
    		// Get response content from file cache
    		$content = file_get_contents($cache_file);
    		$response->setContent($content);
        	$response->addHeader('X-Cache-Html', 'hit');

    	} else {
        	$response = $next($request, $response);
            
            $cacheable = $response->getAttribute('cacheable', $cacheable);

            if ($cacheable) {
            	file_put_contents($cache_file, $response->getContent());
            	$response->addHeader('X-Cache-Html', 'miss');

            } else {
                $response->addHeader('X-Cache-Html', 'not cacheable');
            }
    	}

        return $response;
    }

}
