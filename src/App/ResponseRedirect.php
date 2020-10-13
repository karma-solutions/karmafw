<?php

namespace KarmaFW\App;


class ResponseRedirect extends Response
{
	protected $url = null;
	protected $status = 302;


	public function __construct($url, $status=302)
	{
		parent::__construct('', null); // $content, $content_type

		$this->setStatus($status);
		$this->url = $url;
	}


	public function sendHeaders()
	{
		if ($this->headers_sent || headers_sent()) {
			//error_log("Warning: headers already sent");
			$this->content = '<meta http-equiv="refresh" content="0;URL=' . htmlspecialchars($this->url) . '">';
			//$this->content = '<script>window.location.href = "' . $this->url . '";</script>';
			return;
		}

		$this->content = '';

		$this->headers['Location'] = $this->url;

		parent::sendHeaders();
	}

}
