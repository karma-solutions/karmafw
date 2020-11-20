<?php

namespace KarmaFW\Commands;

//use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class CompileFwCommand
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
		if (! defined('FW_SRC_DIR')) {
			echo "Error: FW_SRC_DIR not found" . PHP_EOL;
			exit;
		}

		echo $this->compileDir(FW_SRC_DIR);
		//echo $this->compileDir(APP_DIR . '/src');
		//echo $this->compileDir(VENDOR_DIR);
	}


	public function compileDir($dir) 
	{

		$cmd = 'grep ^namespace ' . $dir . ' -Rl --include "*.php"';
		$result = trim(shell_exec($cmd));
		$results = explode(PHP_EOL, $result);

		$namespaces = [];

		foreach ($results as $filepath) {
			$content = file_get_contents($filepath);
			//echo $content;

			preg_match('/namespace ([^;]+);/', $content, $match, PREG_OFFSET_CAPTURE);

			if (empty($match[1])) {
				throw new \Exception("no namespace regexp match", 1);
			}

			$namespace = trim($match[1][0]);
			$namespace_pos = $match[1][1];

			if (! isset($namespaces[$namespace])) {
				$namespaces[$namespace] = ['code' => [], 'use' => []];
			}

			$code = explode(";", substr($content, $namespace_pos), 2)[1];

			preg_match_all('~^use ([^;]+);~m', $code, $matches);
			if (empty($matches[1])) {
				// no use found
			} else {
				foreach ($matches[1] as $class_name) {
					$class_name = trim($class_name);
					$namespaces[$namespace]['use'][$class_name] = $class_name;
				}

			}

			$code = preg_replace('~^use [^;]+;~m', '', $code);

			if (strpos($code, '__DIR__') !== false || strpos($code, '__FILE__') !== false) {
				continue;
			}

			if (stripos($code, 'deprecated') !== false) {
				continue;
			}

			$namespaces[$namespace]['code'][$filepath] = $code;
		}


		$output = '<' . '?php' . PHP_EOL;

		foreach ($namespaces as $namespace_name => $namespace) {
			$codes = $namespace['code'];
			$uses = $namespace['use'];

			$namespace_codes = [];
			$namespace_uses = array_map(function ($use) {return "\tuse " . $use . ";";}, $uses);

			foreach ($codes as $filepath => $code) {
				$filepath_short = '.' . str_replace(APP_DIR, '', $filepath);
				$code = ' /* ' . $filepath_short . ' */ ' . $code;
				$namespace_codes[] = $code;
			}

			$namespace_code = implode(PHP_EOL, $namespace_codes);
			$namespace_use = implode(PHP_EOL, $namespace_uses);

			// indent
			$lines = explode(PHP_EOL, $namespace_code);
			$lines = array_map(function ($line) {return "\t" . $line;}, $lines);
			$namespace_code = implode(PHP_EOL, $lines);

			$output .= PHP_EOL . 'namespace ' . $namespace_name . ' {' . PHP_EOL . $namespace_use . PHP_EOL . $namespace_code . PHP_EOL . '}' . PHP_EOL;

		}


		//print_r($namespaces);

		return $output;
	}
	
}
