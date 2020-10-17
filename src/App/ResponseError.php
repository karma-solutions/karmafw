<?php

namespace KarmaFW\App;

throw new \Exception("DEPRECATED", 1);

class ResponseError extends Response
{
	protected $status = 500;
	protected $reasonPhrase = 'Server Error';


	public function __construct($body=null, $status=500, $headers=[], $content_type='text/html')
	{
		parent::__construct($status, $headers, $body);

		$this->setContentType($content_type);

		/*
		if (is_null($body)) {
			$this->body = '<html>';
			$this->body .= '<head><title>' . $this->status . " " . $this->reasonPhrase . '</title></head>';
			$this->body .= '<body>';
			$this->body .= '<h1>' . $this->status . " " . $this->reasonPhrase . '</h1>';
			$this->body .= '</body>';
			$this->body .= '</html>';
		}
		*/
	}

	public function getDefaultBody()
	{
		$body = '<html>';
		$body .= '<head><title>' . $this->status . " " . $this->reasonPhrase . '</title></head>';
		$body .= '<body>';
		$body .= '<h1>Error ' . $this->status . " : " . $this->reasonPhrase . '</h1>';
		$body .= '</body>';
		$body .= '</html>';

		return $body;
	}


}
