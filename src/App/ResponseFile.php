<?php

namespace KarmaFW\App;

use KarmaFW\App;


class ResponseFile extends Response
{
	protected $file_path;


	public function __construct($file_path)
	{
		parent::__construct('', null);

		$this->file_path = $file_path;
	}


	public function sendHeaders()
	{
		if (App::isCli()) {
			return;
		}

		if ($this->headers_sent) {
			// Warning: headers already sent
			//error_log("Warning: headers already sent");
			//return;
		}

		if (! is_file($this->file_path)) {
			// File not found
			$this->headers['Content-Length'] = 0;

		} else {
			$content_type = mime_content_type($this->file_path);
			if (empty($content_type)) {
				$content_type = "application/octet-stream";
			}

			$this->headers['Content-Length'] = filesize($this->file_path);
			$this->headers['Content-Type'] = $content_type;
			$this->headers['Content-Transfer-Encoding'] = "Binary";
			$this->headers['Content-disposition'] = 'attachment; filename="' . basename($this->file_path) . '"';
		}

		parent::sendHeaders();
	}


	public function send()
	{
		if (! is_file($this->file_path)) {
			// ERROR 404
			$this->setStatus(404);
		}

		if ($this->headers_sent) {
			// Warning: redirect may not work
		}

		$this->sendHeaders();

		if (is_file($this->file_path)) {
			readfile($this->file_path);

		} else {
			echo "File not found";
		}
	}


}
