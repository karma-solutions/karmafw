<?php

namespace KarmaFW\App;

use KarmaFW\App;


class ResponseRedirect extends Response
{
	protected $redirect_url = null;
	protected $status = 302;


	public function __construct($url, $status=302)
	{
		parent::__construct($status);

		$this->redirect_url = $url;
	}


	public function sendHeaders()
	{
		if (App::isCli()) {
			echo "# HTTP REDIRECTION " . $this->status . " TO " . $this->redirect_url . PHP_EOL;
			return;
		}

		if ($this->headers_sent || headers_sent()) {
			//error_log("Warning: headers already sent");
			$this->content = '<meta http-equiv="refresh" content="0;URL=' . htmlspecialchars($this->redirect_url) . '">';
			//$this->content = '<script>window.location.href = "' . $this->redirect_url . '";</script>';
			return;
		}

		$this->content = '';

		$this->headers['Location'] = $this->redirect_url;

		parent::sendHeaders();
	}

}
