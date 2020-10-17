<?php

namespace KarmaFW\App;

throw new \Exception("DEPRECATED", 1);

class ResponseError404 extends ResponseError
{
	protected $status = 404;
	protected $reasonPhrase = 'Not Found';


	public function __construct($body=null, $headers=[], $content_type='text/html')
	{
		parent::__construct($body, $this->status, $headers, $content_type);
	}

}
