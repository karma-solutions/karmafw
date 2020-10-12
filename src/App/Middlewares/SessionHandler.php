<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;


class SessionHandler
{
	
	public function __invoke(Request $request, Response $response, callable $next)
	{
		//$savePath = ini_get('session.save_path');
        //ini_set('session.save_path', $savePath);
        //ini_set('session.save_handler', 'files');

		session_start();
		
		return $next($request, $response);
	}

}
