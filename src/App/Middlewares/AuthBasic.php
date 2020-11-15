<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class AuthBasic
{
    protected $method;


    public function __construct($method='basic')
    {
        $this->method = $method;
    }


    public function __invoke(Request $request, Response $response, callable $next)
    {
        $is_auth = 1;

        $PHP_AUTH_USER = isset($request->SERVER['PHP_AUTH_USER']) ? $request->SERVER['PHP_AUTH_USER'] : null;
        $PHP_AUTH_PW = isset($request->SERVER['PHP_AUTH_PW']) ? $request->SERVER['PHP_AUTH_PW'] : null;

        if (! $PHP_AUTH_USER || ! $PHP_AUTH_PW) {
            $is_auth = false;
        }


        // TODO: recuperer les identifiants valides dans une base de données ou un fichier de passwords


        if ($is_auth) {
            return $next($request, $response);

        } else {
            $response->addHeader('WWW-Authenticate', 'Basic realm="My Realm"');
            $body = "Accès interdit";
            return $response->html($body, 401);
        }
    
    }

}
