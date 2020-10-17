<?php

namespace KarmaFW\App;

throw new \Exception("DEPRECATED", 1);


class ResponseHtml extends Response
{

	public function __construct($body=null, $status=200, $headers=[], $content_type='text/html')
	{
		parent::__construct($status, $headers, $body);

		$this->setContentType($content_type);
	}

}
