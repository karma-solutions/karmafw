<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;


class ErrorHandler
{
    
    public function __invoke(Request $request, Response $response, callable $next)
    {
        //set_error_handler(['ErrorHandler', 'display']);
        //set_exception_handler(['ExceptionHandler', 'display']);

        if (false) {
            $whoops = new \Whoops\Run;
            $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        }

        try {
            $response = $next($request, $response);

        } catch (\Throwable $e) {
            echo "ErrorHandler CATCHED EXCEPTION" . PHP_EOL; // TODO
            print_r($e);
        }

        return $response;
    }

}
