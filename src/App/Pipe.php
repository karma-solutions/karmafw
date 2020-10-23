<?php

namespace KarmaFW\App;

use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


// https://mnapoli.fr/presentations/forumphp-middlewares/
// https://github.com/oscarotero/psr7-middlewares


class Pipe
{
    protected $services = [];


    public function __construct(array $services=[])
    {
        $this->services = $services;
    }


    public function process(Request $request, Response $response)
    {
        return $this->next($request, $response);
    }

    public function next(Request $request, Response $response)
    {
        if (! $this->services) {
        	//throw new \Exception("no more service", 1);
            return $response;
        }

        $service = array_shift($this->services);
        $service_name = get_class($service);

        $debugbar = App::getData('debugbar');
        if ($debugbar) {
            if (isset($debugbar['time'])) {
                $debugbar['time']->startMeasure($service_name, $service_name);
            }
        }

        $response = call_user_func($service, $request, $response, [$this, 'next']);

        $debugbar = App::getData('debugbar');
        if ($debugbar) {
            if (isset($debugbar['time']) && $debugbar['time']->hasStartedMeasure($service_name)) {
                $debugbar['time']->stopMeasure($service_name);
            }
        }

        return $response;
    }
}
