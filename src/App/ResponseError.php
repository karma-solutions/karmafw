<?php

namespace KarmaFW\App;


class ResponseError extends Response
{
	protected $status = 500;
	protected $status_name = 'Server Error';


	public function __construct($status=500, $content=null, $content_type='text/html')
	{
		parent::__construct($content, $content_type);

		if (is_null($content)) {
			$this->content = '<h1>' . $this->status_name . '</h1>';
		}

		$this->setStatus($status);
	}

}
