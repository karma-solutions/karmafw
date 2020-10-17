<?php

namespace KarmaFW\Controllers;

use \KarmaFW\Routing\Controllers\WebAppController;
use \KarmaFW\App\Middlewares\MinimifierJs;
use \KarmaFW\App\Middlewares\MinimifierCss;


class MinimifierController extends WebAppController
{
	
	public function minimifier_js($arguments=[])
	{
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
				return $this->response->error404("Invalid file");
			}

			if (! is_file($file_path)) {
				// file not found
				return $this->response->error404("File not found");

			} else {
				if (false) {
					// NO minimification
					readfile($file_path);

				} else {
					// minimification
					$content = file_get_contents($file_path);
					$this->response->setContent($content);
		            $content_length = $this->response->getContentLength();

					$content_minimified = MinimifierJs::minify_js($content);
					$this->response->setContent($content_minimified);
		            $content_minimified_length = $this->response->getContentLength();
					
					$this->response->addHeader('Content-Type', 'text/javascript');

		            // add information headers
		            $this->response->addHeader('X-CSS-Unminimified-Content-Length', $content_length);
		            $this->response->addHeader('X-CSS-Minimified-Content-Length', $content_minimified_length);

					// TODO: gerer cache-expire, expires, ...
				}
			}

		} else {
			// Error document root not found
			return $this->response->error404("Root not found");
		}
	}

	
	public function minimifier_css($arguments=[])
	{
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
				return $this->response->error404("Invalid file");
			}

			if (! is_file($file_path)) {
				// file not found
				return $this->response->error404("File not found");

			} else {
				if (false) {
					// NO minimification
					readfile($file_path);

				} else {
					// minimification
					$content = file_get_contents($file_path);
					$this->response->setContent($content);
		            $content_length = $this->response->getContentLength();

					$content_minimified = MinimifierCss::minify_css($content);
					$this->response->setContent($content_minimified);
		            $content_minimified_length = $this->response->getContentLength();
					
					$this->response->addHeader('Content-Type', 'text/css');

		            // add information headers
		            $this->response->addHeader('X-CSS-Unminimified-Content-Length', $content_length);
		            $this->response->addHeader('X-CSS-Minimified-Content-Length', $content_minimified_length);

					// TODO: gerer cache-expire, expires, ...
				}
			}

		} else {
			// Error document root not found
			return $this->response->error404("Root not found");
		}
	}

}
