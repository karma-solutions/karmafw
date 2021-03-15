<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class SessionHandler
{
	
	public function __invoke(Request $request, Response $response, callable $next)
	{
		//$savePath = ini_get('session.save_path');
		//ini_set('session.save_path', $savePath);
		//ini_set('session.save_handler', 'files');

		/*
		// Pour utiliser Redis
		ini_set('session.save_handler, "redis");
		ini_set('session.save_path, "tcp://host1:6379?weight=1, tcp://host2:6379?weight=2&timeout=2.5, tcp://host3:6379?weight=2&read_timeout=2.5");
		*/

		$session_gc_maxlifetime = ini_get('session.gc_maxlifetime');
		$session_duration = $session_gc_maxlifetime ? $session_gc_maxlifetime : 3600/2;
		session_set_cookie_params($session_duration);

		session_start();
		
		$response = $next($request, $response);

		session_write_close();

		return $response;
	}

}
