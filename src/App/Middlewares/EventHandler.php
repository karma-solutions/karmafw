<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class EventHandler
{    

    public function __construct()
    {

    }


    public function __invoke(Request $request, Response $response, callable $next)
    {
    	// TODO

        $response = $next($request, $response);

        return $response;
    }

}


/*



$dispatcher->addListener(
    Event\ApplicationEvent::BEFORE_CALL_CONTROLLER,
    [new ApplicationHandler(), 'tracking']
);


self::$eventDispatcher->dispatch(
    ApplicationEvent::BEFORE_CALL_CONTROLLER,
    new ApplicationEvent($request, new Response())
);


*/