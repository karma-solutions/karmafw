<?php

namespace KarmaFW\App;


class Pipe
{
    protected $services = [];


    public function __construct($services=[])
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
        $response = call_user_func($service, $request, $response, [$this, 'next']);
        return $response;
    }
}
