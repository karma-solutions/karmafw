<?php

namespace KarmaFW\Commands;

//use \KarmaFW\App;
use \KarmaFW\App\Request;
use \KarmaFW\App\Response;


class TestCommand
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
		print_r($arguments);
		
	}
	
}
