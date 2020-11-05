<?php

namespace KarmaFW\Commands;

//use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class ControllerCommand
{
	protected $request;
	protected $response;


	public function __construct(Request $request, Response $response) 
	{
		$this->request = $request;
		$this->response = $response;
	}


	public function execute($arguments=[]) 
	{

		
	}
	
}
