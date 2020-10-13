<?php

namespace KarmaFW\App;


class ResponseError404 extends ResponseError
{
	protected $status = 404;
	protected $status_name = 'Not Found';


	public function __construct($content=null, $content_type='text/html')
	{
		parent::__construct($this->status, $content, $content_type);
	}

}
