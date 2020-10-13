<?php

namespace KarmaFW\Commands;

//use \KarmaFW\App;
use \KarmaFW\App\Request;
use \KarmaFW\App\Response;


class HelpCommand
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
		echo "Usage: php console.php <command> [arguments]" . PHP_EOL;
		echo PHP_EOL;
		echo "Example: php console.php test param1 param2" . PHP_EOL;
		echo PHP_EOL;
		echo "Example: php console.php MonScript param1 param2" . PHP_EOL;
		echo "Example: php console.php mon_script param1 param2" . PHP_EOL;
		echo PHP_EOL;
		echo " => execute \\App\\Commands\\MonScript.php" . PHP_EOL;
		echo "         or \\App\\Commands\\MonScriptCommand.php" . PHP_EOL;
		echo "         or \\KarmaFW\\Commands\\MonScript.php" . PHP_EOL;
		echo "         or \\KarmaFW\\Commands\\MonScriptCommand.php" . PHP_EOL;
		echo PHP_EOL;
		
	}
	
}
