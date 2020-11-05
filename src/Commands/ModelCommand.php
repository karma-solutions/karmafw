<?php

namespace KarmaFW\Commands;

use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class ModelCommand
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
		if (empty($arguments)) {
			echo "PHP Console script" . PHP_EOL;
			echo PHP_EOL;
			echo "Usage: php console.php model [model_name]" . PHP_EOL;
			echo PHP_EOL;
			echo "Example: php console.php model users" . PHP_EOL;
			echo PHP_EOL;
			return;
		}

		$table_name = $arguments[0];

		$class_name = $table_name;
		$class_name = strtolower($class_name);
		$class_name = rtrim($class_name, 's');
		$class_name = ucwords($class_name, "_ \t\r\n\f\v");
		$class_name = str_replace('s_', '', $class_name);
		$class_name = str_replace('_', '', $class_name);
		//echo $class_name . PHP_EOL; exit;

		$db = App::getDb();

		$columns = $db->listTableColumns($table_name);
		//print_r($columns);
		$infos = [];
		foreach ($columns as $column) {
			$infos[] = '- ' . $column['Field'] . ' => ' . $column['Type'];
		}

		$indexes = $db->listTableIndexes($table_name);
		$primary_key = [];
		foreach ($indexes as $index) {
			if ($index['Key_name'] == 'PRIMARY') {
				$seq = $index['Seq_in_index'];
				$primary_key[$seq] = $index['Column_name'];
			}
		}
		ksort($primary_key);
		$primary_key = array_values($primary_key);
		//print_r($primary_key);

		$tpl = '<' . '?php

namespace App\Models;

use \KarmaFW\Database\Sql\SqlTableModel;


/*
Fields:
' . implode(PHP_EOL, $infos) . '
*/


class ' . $class_name . ' extends SqlTableModel
{
	public static $table_name = "' . $table_name . '";
	public static $primary_key = ["' . implode('", "', $primary_key) . '"];

}
';

		echo $tpl;		
	}
	
}
