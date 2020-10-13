<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\App\Request;
use \KarmaFW\App\Response;
use \KarmaFW\App\ResponseError404;
use \KarmaFW\App\ResponseFile;


class CommandRouter
{
	protected $argv;


	public function __construct($argv=[])
	{
		$this->argv = $argv;
	}
	

	public function __invoke(Request $request, Response $response, callable $next)
	{
		$arguments = array_slice($this->argv, 0);
		$script_name = array_shift($arguments);

		$command_name = array_shift($arguments);
		if (in_array($command_name, ['-h', '--help', '-help'])) {
			$command_name = 'help';
		}
		
		$class_name = implode('', array_map('ucfirst', explode("_", $command_name)));

		if (! empty($class_name)) {
			$class_user = '\\App\\Commands\\' . $class_name;
			$class_fw = '\\KarmaFW\\Commands\\' . $class_name;

			if (class_exists($class_user)) {
				// User command
				$command = new $class_user($request, $response);
				$command->execute($arguments);

			} else if (class_exists($class_user . "Command")) {
				// User command
				$class_user .= "Command";
				$command = new $class_user($request, $response);
				$command->execute($arguments);

			} else if (class_exists($class_fw)) {
				// Framework command
				$command = new $class_fw($request, $response);
				$command->execute($arguments);

			} else if (class_exists($class_fw . "Command")) {
				// Framework command
				$class_fw .= "Command";
				$command = new $class_fw($request, $response);
				$command->execute($arguments);

			} else {
				$this->usage("invalid command : " . $command_name);
			}

		} else {
			$this->usage("missing command");
		}

		return $response;
	}
	

	protected function usage($error)
	{
		echo "PHP Console script" . PHP_EOL . PHP_EOL; 
		echo "Usage: php console.php <command> [arguments]" . PHP_EOL . PHP_EOL;

		if ($error) {
			echo "Warning: " . $error . PHP_EOL;
		}
	}

}
