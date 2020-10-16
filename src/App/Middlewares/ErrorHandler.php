<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;
use \KarmaFW\App\ResponseError;
use \KarmaFW\App\ResponseError404;


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
            $code = $e->getCode();
            $error_message = $e->getMessage();
            
            //throw $e;
            $content = null;

            if (ENV == 'dev') {
                //$title = "ErrorHandler CATCHED EXCEPTION";
                //$message = '<pre>' . print_r($e, true) . '</pre>';
                //$content = '<title>' . $title . '</title><h1>' . $title . '</h1><p>' . $message . '</p>';
            }

            if ($code == 404) {
                return new ResponseError404($error_message);
            }

            return new ResponseError(500, $content);
        }

        return $response;
    }

}
