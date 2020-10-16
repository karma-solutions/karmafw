<?php

namespace KarmaFW\App;


class ResponseText extends Response
{

	public function __construct($body=null, $status=200, $content_type='text/plain', $headers=[])
	{
		$headers['Content-Type'] = $content_type;

		parent::__construct($status, $headers, $body);
	}

}
