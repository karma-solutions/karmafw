<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


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

        } catch (\Exception $e) {
            $error_code = $e->getCode();
            $error_message = $e->getMessage();


            // TODO: voir comment bien injecter cette dependance
            $debugbar = App::getData('debugbar');
            if ($debugbar) {
                if (isset($debugbar['exceptions'])) {
                    $debugbar['exceptions']->addException($e);
                }
            }


            /*
            if ($error_code == 404) {
                // case moved to UrlRouter
                $response->setStatus(404)->html($error_message);
            }
            */

            if (! $this->use_internal_handler) {
                throw $e;
            }

            $http_code = (500 <= $error_code && $error_code <= 599) ? $error_code : 500;
            
            error_log("[UrlRouter Error] " . $error_code . " : " . $error_message);


            if (ENV == 'dev') {
                $title = "ErrorHandler CATCHED EXCEPTION CODE " . $error_code;
                $message = '<pre>' . print_r($e, true) . '</pre>';
                $response_content = '<title>' . $title . '</title><h1>' . $title . '</h1><h2>' . $error_message . '</h2><p>' . $message . '</p>';

            } else {
                $title = "Server Error";
                $message = 'An error has occured';
                $response_content = '<title>' . $title . '</title><h1>' . $title . '</h1><p>' . $message . '</p>';
            }

			if (App::isCli()) {
				echo $title . PHP_EOL . PHP_EOL;
				echo $message . PHP_EOL . PHP_EOL;
				exit;
			}

            $response->html($response_content, $http_code);
        }

        return $response;
    }

}
