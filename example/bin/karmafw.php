<?php

echo "DEPRECATED" . PHP_EOL; exit;


function help($exit=false) {
	echo "Usage: php karmakw.php <action> [options]" . PHP_EOL;
	echo PHP_EOL;
	echo "  actions:" . PHP_EOL;
	echo "     new-controller <item_name_single> <item_name_plural>" . PHP_EOL;
	echo "     new-model <item_name_single> <item_name_plural>" . PHP_EOL;
	echo "     new-migration <migration_name>" . PHP_EOL;
	echo PHP_EOL;
	echo "  example:" . PHP_EOL;
	echo "     php karmakw.php new-controller user users" . PHP_EOL;
	echo PHP_EOL;

	if ($exit) {
		exit();
	}
}

function pre($var, $exit = false, $prefix = '') {
	echo "<pre>";
	if (!empty($prefix)) {
		echo $prefix;
	}
	if (is_null($var)) {
		echo "NULL";
	} else if ($var === true) {
		echo "TRUE";
	} else if ($var === false) {
		echo "FALSE";
	} else if (is_string($var)) {
		echo '"' . $var . '"';
	} else {
		print_r($var);
	}
	echo "</pre>";

	if ($exit) {
		exit;
	}
}


/* ###### */

if (empty($argv[1])) {
	help(true);
}

$action = $argv[1];

