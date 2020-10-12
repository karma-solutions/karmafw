<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;


class ResponseTime
{
    
    public function __invoke(Request $request, Response $response, callable $next)
    {
        if (! isset($request->SERVER['REQUEST_TIME_FLOAT'])) {
            $request->SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
        }

        $response = $next($request, $response);

        $ts_end = microtime(true);
        $duration = $ts_end - $request->SERVER['REQUEST_TIME_FLOAT'];

        $response->addHeader('X-Response-Time', round($duration, 4));

        return $response;
    }

}
