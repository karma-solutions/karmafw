<?php

use \KarmaFW\App;


if (! function_exists('arrayReduceToOneColumn')) {
	function arrayReduceToOneColumn($array, $column_key) {
		return array_map(function($row) use ($column_key) {
			if (is_callable($column_key)) {
				return $column_key($row);

			} else {
				return $row[$column_key];
			}
		}, $array);
	}
}

if (! function_exists('arrayAddKeyFromColumn')) {
	function arrayAddKeyFromColumn($array, $column_key) {
		$results = array();
		foreach ($array as $row) {
			if (is_callable($column_key)) {
				$key = $column_key($row);
			} else if (is_array($column_key)) {
				$key_parts = [];
				foreach ($column_key as $column_key_item) {
					$key_parts[] = $row[$column_key_item];
				}
				$key = implode('-', $key_parts);
			}else{
				$key = $row[$column_key];
			}
			$results[$key] = $row;
		}
		if (empty($results)) {
			//return new stdClass();
		}
		return $results;
	}
}

if (! function_exists('arrayGroupByColumn')) {
	function arrayGroupByColumn($array, $column_key) {
		$results = array();
		foreach ($array as $k => $v) {
			if (is_callable($column_key)) {
				$key_value = $column_key($v);
			} else {
				$key_value = $v[$column_key];
				
			}
			if (! isset($results[$key_value])) {
				$results[$key_value] = array();
			}
			$results[$key_value][$k] = $v;
		}
		return $results;
	}
}

if (! function_exists('arrayToList')) {
	function arrayToList($array) {
		$results = array();
		$db = App::getDb();

		foreach ($array as $k => $v) {
			$results[] = $db->escape($v);
		}

		return implode(', ', $results);
	}
}


if (! function_exists('get_csv')) {
	function get_csv($arr, $fields=array(), $sep=";") {
		$str = '';
		if (! empty($arr)) {

			if (empty($fields)) {
				$fields = array_keys($arr[0]);
			}

			$line = array();
			foreach ($fields as $k => $v) {
				if (! is_numeric($k)) {
					$line[] = $k;

				} else {
					$line[] = $v;
				}
			}
			$str .= implode($sep, $line) . PHP_EOL;

			foreach ($arr as $row) {
				$line = array();
				foreach ($fields as $field) {
					$val = $row[$field];
					if (is_numeric($val) && substr($val."", 0, 1) === "0" && strlen($val."") > 1) {
						// pour exporter correctement dans Excel les numeros de telephone commencant par 0
						$val = '="' . $val . '"';
					}
					$line[] = $val;
				}
				//$str .= implode($sep, $line) . PHP_EOL;
				//$str .= '"' . implode('"' . $sep . '"', str_replace('"', '\\"', $line)) . '"' . PHP_EOL;
				$str .= '"' . implode('"' . $sep . '"', str_replace('"', '""', $line)) . '"' . PHP_EOL;
			}
		}
		return $str;
	}
}


if (! function_exists('exportToCsvFile')) {
	function exportToCsvFile($rows, $export_filename=null, $fields=null) {
		if (! empty($export_filename)) {
			// download file
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment;filename=' . basename($export_filename));
			header("Pragma: no-cache");
			header("Expires: 0");
		} else {
			// show in browser
			header('Content-Type: text/plain');
		}

		echo get_csv($rows, $fields);
		exit;
	}
}
