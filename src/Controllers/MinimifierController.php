<?php

namespace KarmaFW\Controllers;

use \KarmaFW\Routing\Controllers\WebAppController;
use \KarmaFW\App\Middlewares\MinimifierJs;


class MinimifierController extends WebAppController
{
	
	public function minimifier_js($arguments=[])
	{
		//pre($arguments, 1);

		$file_url = $arguments['file_url'];

		$document_root = APP_DIR . '/public';

		if (! is_dir($document_root)) {
			if (! empty($_SERVER['DOCUMENT_ROOT'])) {
				$document_root = $_SERVER['DOCUMENT_ROOT'];

			} else {
				$document_root = '';
			}
		}

		if ($document_root) {
			$file_path = $document_root . $file_url;

			if (! is_file($file_path)) {
				header('HTTP/1.1 404 Not Found');
				echo "ERROR 404";
				exit;

			} else {
				header('Content-type: text/javascript');

				if (false) {
					// NO minimification
					readfile($file_path);

				} else {
					// minimification
					$content = file_get_contents($file_path);
					$content = MinimifierJs::minify_js($content);
					echo $content;
				}
			}
		}

	}

}
