<?php

use \KarmaFW\App;
use \KarmaFW\App\ResponseText;
use \PhpOffice\PhpSpreadsheet\IOFactory as PhpSpreadsheetIOFactory;


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
	function arrayGroupByColumn($array, $column_key, $preserve_keys=true) {
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
			if ($preserve_keys) {
				$results[$key_value][$k] = $v;
				
			} else {
				$results[$key_value][] = $v;
			}
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


if (! function_exists('import_xls')) {
	function import_xls($filepath, $fields=[], $encode_utf8=false) {
		$spreadsheet = PhpSpreadsheetIOFactory::load($filepath);

		//$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		//$reader->setReadDataOnly(true);
		//$spreadsheet = $reader->load($filepath);
		
		$sheetData = $spreadsheet->getActiveSheet()->toArray();
		//pre($sheetData, 1);

		$headers = [];
		if (! empty($fields)) {
			$headers = $fields;
		}

		$rows = [];
		$line_idx = 0;
		foreach ($sheetData as $input_row) {
			$line_idx++;

			if ($line_idx <= 1) {
				// ligne d'entete
				if (empty($headers)) {
					foreach ($input_row as $key => $value) {
						if ($encode_utf8) {
							$headers[$key] = utf8_encode($value);
						} else {
							$headers[$key] = $value;
						}
					}
				}

				continue;

			}else{
				// lignes de data
				$row = [];
				$row_ok = true;
				$col_idx = 0;
				foreach ($input_row as $key => $value) {
					$col_idx++;

					if ($col_idx > count($headers)) {
						continue;
					}
					if (! isset($headers[$key])) {
						$row_ok = false;
						//pre($input_row);
						break;
					}
					$header_key = $headers[$key];
					if ($encode_utf8) {
						$row[$header_key] = utf8_encode($value);
						
					} else {
						$row[$header_key] = $value;
					}
				}

				if ($row_ok) {
					$rows[] = $row;
				}

			}

		}

		return $rows;
	}
}


if (! function_exists('import_csv')) {
	function import_csv($filepath, $separator=";", $fields=[], $encode_utf8=true) {
		$rows = [];

		$handle = fopen($filepath, "r");

		$headers = [];
		if (! empty($fields)) {
			$headers = $fields;
		}

		$line_idx = 0;
		while (($input_row = fgetcsv($handle, 4096, $separator)) !== false) {
			$line_idx++;

			if ($line_idx <= 1) {
				// ligne d'entete
				if (empty($headers)) {
					foreach ($input_row as $key => $value) {
						if ($encode_utf8) {
							$headers[$key] = utf8_encode($value);
						} else {
							$headers[$key] = $value;
						}
					}
				}

				continue;

			}else{
				// lignes de data
				$row = [];
				foreach ($input_row as $key => $value) {
					$header_key = $headers[$key];
					if ($encode_utf8) {
						$row[$header_key] = utf8_encode($value);
						
					} else {
						$row[$header_key] = $value;
					}
				}

				$rows[] = $row;

			}
		}



		fclose($handle);

		return $rows;
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
					if (is_numeric($val) && strlen($val."") > 1) {
						// pour exporter correctement dans Excel les valeurs numériques
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
		$csv_content = get_csv($rows, $fields);

		
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

		echo $csv_content;
		exit;
		

		/*
		if (! empty($export_filename)) {
			$content_type = 'text/csv';
			$headers = [
				'Content-Disposition: attachment;filename=' . basename($export_filename),
				"Pragma: no-cache",
				"Expires: 0"
			];

		} else {
			$headers = [];
			$content_type = 'text/plain';
		}

		throw new ResponseText($csv_content, 200, $content_type, $headers);
		*/

	}
}


if (! function_exists('array_map_with_keys')) {
	function array_map_with_keys($func, $array) {
		$ret = [];
		foreach ($array as $k => $v) {
			$ret[$k] = $func($v, $k);
		}
		return $ret;
	}
}

