<?php

namespace KarmaFW\App;


class ResponseHtml extends ResponseText
{

	public function __construct($body=null, $status=200)
	{
		parent::__construct($body, $status, 'text/html');
	}

}
