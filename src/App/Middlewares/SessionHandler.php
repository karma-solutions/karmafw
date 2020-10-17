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

		session_start();
		
		$response = $next($request, $response);

		session_write_close();

		return $response;
	}

}
