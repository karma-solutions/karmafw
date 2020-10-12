<?php

namespace KarmaFW\App;


class ResponseRedirect extends Response
{
	protected $url = null;
	protected $status = 302;


	public function __construct($url, $status=302)
	{
		parent::__construct('', null); // $content, $content_type

		$this->setStatus($status)
		$this->url = $url;
	}

	public function sendHeaders()
	{
		if ($this->headers_sent) {
			error_log("Warning: headers already sent");
			return;
		}

		$this->headers['Location'] = $this->url;

		parent::sendHeaders();
	}

	public function send()
	{
		if ($this->headers_sent) {
			// Warning: redirect may not work
		}

		$this->sendHeaders();
	}


}
