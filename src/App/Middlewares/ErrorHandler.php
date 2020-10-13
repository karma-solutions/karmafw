<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;
use \KarmaFW\App\ResponseError;


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
            $content = null;

            if (ENV == 'dev') {
                $title = "ErrorHandler CATCHED EXCEPTION";
                $message = '<pre>' . print_r($e, true) . '</pre>';
                $content = '<title>' . $title . '</title><h1>' . $title . '</h1><p>' . $message . '</p>';
            }

            //throw $e;
            return new ResponseError(500, $content);        }

        return $response;
    }

}
