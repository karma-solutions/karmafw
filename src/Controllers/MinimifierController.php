<?php

namespace KarmaFW\Controllers;

use \KarmaFW\Routing\Controllers\WebAppController;
use \KarmaFW\App\Middlewares\MinimifierJs;
use \KarmaFW\App\Middlewares\MinimifierCss;


class MinimifierController extends WebAppController
{
	
	public function minimifier_js($arguments=[])
	{
		//pre($arguments, 1);

		$file_url = $arguments['file_url'];

		$document_root = APP_DIR . '/public';

		if (! is_dir($document_root)) {
			if (! empty($_SERVER['DOCUMENT_ROOT'])) {
				$document_root = realpath($_SERVER['DOCUMENT_ROOT']);

			} else {
				$document_root = '';
			}
		}

		if ($document_root) {
			$file_path = $document_root . $file_url;

			if ($file_path != realpath($file_path) || substr($file_path, -3) != '.js') {
				// file path invalid or not a js file
				redirect($file_url);
				/*
				header('HTTP/1.1 404 Not Found');
				echo "ERROR 404";
				exit;
				*/
			}

			if (! is_file($file_path)) {
				// file not found
				redirect($file_url);
				/*
				header('HTTP/1.1 404 Not Found');
				echo "ERROR 404";
				exit;
				*/

			} else {
				header('Content-type: text/javascript');

				//if (ENV == 'dev' && ! (defined('MINIMIFY_JS') && MINIMIFY_JS ) ) {
				if (false) {
					// NO minimification
					readfile($file_path);

				} else {
					// minimification
					$content = file_get_contents($file_path);
					$content = MinimifierJs::minify_js($content);
					echo $content;

					// TODO: gerer cache-expire, expires, ...
				}
			}
		}
	}

	
	public function minimifier_css($arguments=[])
	{
		//pre($arguments, 1);

		$file_url = $arguments['file_url'];

		$document_root = APP_DIR . '/public';

		if (! is_dir($document_root)) {
			if (! empty($_SERVER['DOCUMENT_ROOT'])) {
				$document_root = realpath($_SERVER['DOCUMENT_ROOT']);

			} else {
				$document_root = '';
			}
		}

		if ($document_root) {
			$file_path = $document_root . $file_url;

			if ($file_path != realpath($file_path) || substr($file_path, -4) != '.css') {
				// file path invalid or not a css file
				redirect($file_url);
				/*
				header('HTTP/1.1 404 Not Found');
				echo "ERROR 404";
				exit;
				*/
			}

			if (! is_file($file_path)) {
				// file not found
				redirect($file_url);
				/*
				header('HTTP/1.1 404 Not Found');
				echo "ERROR 404";
				exit;
				*/

			} else {
				header('Content-type: text/css');

				if (false) {
					// NO minimification
					readfile($file_path);

				} else {
					// minimification
					$content = file_get_contents($file_path);
					$content = MinimifierCss::minify_css($content);
					echo $content;

					// TODO: gerer cache-expire, expires, ...
				}
			}

		} else {
			// Error document root not found
			redirect($file_url);
		}
	}

	protected function redirectToUrl($url)
	{
		$continue_url = "";
		redirect($continue_url);
		//return new ResponseRedirect($continue_url, 302);
	}

}
