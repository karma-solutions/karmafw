<?php

namespace KarmaFW\App;

throw new \Exception("DEPRECATED", 1);


class ResponseHtml extends ResponseThrowable
{

	public function __construct($body=null, $status=200, $headers=[], $content_type='text/plain')
	{
		parent::__construct($status, $headers, $body);

		$this->setContentType($content_type);
	}

}
