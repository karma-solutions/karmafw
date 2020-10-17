<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;


class ErrorHandler
{
    protected $use_internal_handler;
    protected $use_whoops_handler;
    

    public function __construct($use_internal_handler=true, $use_whoops_handler=false)
    {
        $this->use_internal_handler = $use_internal_handler;
        $this->use_whoops_handler = $use_whoops_handler;
    }


    public function __invoke(Request $request, Response $response, callable $next)
    {
        //set_error_handler(['ErrorHandler', 'display']);
        //set_exception_handler(['ExceptionHandler', 'display']);

        if ($this->use_whoops_handler) {
            $whoops = new \Whoops\Run;
            $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        }

        try {
            $response = $next($request, $response);

        } catch (\Throwable $e) {
            $error_code = $e->getCode();
            $error_message = $e->getMessage();


            $is_response = is_a($e, Response::class);
            if ($is_response) {
                // exception is in reality a Response
                return $e;
            }

            /*
            if ($error_code == 404) {
                // case moved to UrlRouter
                $response->setStatus(404)->setHtml($error_message);
            }
            */

            if (! $this->use_internal_handler) {
                throw $e;
            }

            $http_code = (500 <= $error_code && $error_code <= 599) ? $error_code : 500;
            
            error_log("[UrlRouter] Error " . $error_code . " : " . $error_message);


            if (ENV == 'dev') {
                $title = "ErrorHandler CATCHED EXCEPTION CODE " . $error_code;
                $message = '<pre>' . print_r($e, true) . '</pre>';
                $response_content = '<title>' . $title . '</title><h1>' . $title . '</h1><h2>' . $error_message . '</h2><p>' . $message . '</p>';

            } else {
                $title = "Server Error";
                $message = 'An error has occured';
                $response_content = '<title>' . $title . '</title><h1>' . $title . '</h1><p>' . $message . '</p>';
            }

            $response->setStatus($http_code)->setHtml($response_content);
        }

        return $response;
    }

}
