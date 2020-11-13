<?php

namespace KarmaFW\Commands;

//use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class MigrationCommand
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
		echo "PHP Console script" . PHP_EOL;
		echo PHP_EOL;
		echo "Usage: php console.php migration [migration_name]" . PHP_EOL;
		echo PHP_EOL;
		echo "Example: php console.php migration add_column_age_into_table_users" . PHP_EOL;
		echo PHP_EOL;

		
	}
	
}
