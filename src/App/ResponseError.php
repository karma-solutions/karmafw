<?php

namespace KarmaFW\App;


class ResponseError extends ResponseText
{
	protected $status = 500;
	protected $reasonPhrase = 'Server Error';


	public function __construct($status=500, $body=null, $content_type='text/html')
	{
		parent::__construct($body, $status, $content_type);

		if (is_null($body)) {
			$this->body  = '<title>' . $this->status . " " . $this->reasonPhrase . '</title>';
			$this->body .= '<h1>' . $this->status . " " . $this->reasonPhrase . '</h1>';
		}

		$this->setStatus($status);
	}

}
