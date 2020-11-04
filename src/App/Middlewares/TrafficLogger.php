<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class TrafficLogger
{
	
	public function __invoke(Request $request, Response $response, callable $next)
	{
        if (! isset($request->SERVER['REQUEST_TIME_FLOAT'])) {
            $request->SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
        }

		$response = $next($request, $response);

        $ts_end = microtime(true);
        $duration = $ts_end - $request->SERVER['REQUEST_TIME_FLOAT'];

		$traffic_logger = App::getData('app')->get('traffic_logger');

		if ($traffic_logger) {
			$traffic_logger($request, $response, $duration);
		}

		return $response;
	}

}
