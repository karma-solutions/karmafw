<?php

namespace KarmaFW\App;

use KarmaFW\App;

// TODO: a remplacer par ou rendre compatible avec GuzzleHttp\Psr7\Response


class Response extends \Exception
{
	protected $headers = [];
	protected $body = '';
	protected $status = 200;
	protected $reasonPhrase = 'OK';
	protected $content_type = '';
	protected $headers_sent = false;
	protected $protocol = null;
	protected $template_path = null;
	protected $template_data = [];


	/* public */ const http_status_codes = [
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		103 => 'Early Hints',

		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',

		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',

		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway ou Proxy Error',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version not supported',
	];


	public function __construct($status=200, array $headers=[], $body=null, $version='1.1', $reason=null)
	{
		$this->body = $body;
		$this->setStatus($status);
		$this->setHeaders($headers);

        $this->protocol = $version;
	}


	public function getStatus()
	{
		return $this->status;
	}

	public function getProtocol()
	{
		return $this->protocol;
	}

	public function setProtocol($protocol)
	{
		$this->protocol = $protocol;
		return $this;
	}

	public function getReasonPhrase()
	{
		return $this->reasonPhrase;
	}

	public function setStatus($status=200)
	{
		$this->status = $status;

		$reasonPhrase = ! empty(self::http_status_codes[$status]) ? self::http_status_codes[$status] : "Unknown status";
		$this->reasonPhrase = $reasonPhrase;

		return $this;
	}

	public function getContentType()
	{
		return $this->content_type;
	}

	public function setContentType($content_type)
	{
		$this->content_type = $content_type;
		return $this;
	}

	public function getTemplatePath()
	{
		return $this->template_path;
	}

	public function setTemplatePath($template_path)
	{
		$this->template_path = $template_path;
		return $this;
	}

	public function getTemplateData()
	{
		return $this->template_data;
	}

	public function setTemplateData($template_data)
	{
		$this->template_data = $template_data;
		return $this;
	}

	public function getContent()
	{
		// DEPRECATED
		return $this->getBody(); 
	}

	public function getBody()
	{
		return $this->body;
	}

	public function getContentLength()
	{
		return strlen($this->body);
	}

	public function setBody($body)
	{
		$this->body = $body;
		return $this;
	}

	public function setContent($body)
	{
		// DEPRECATED
		return $this->setBody($body);
	}

	public function setHtml($body, $status=200, $content_type='text/html')
	{
		return $this->setBody($body)
				->setContentType($content_type)
				->setStatus($status);
	}
	
	public function setJson($body, $status=200, $content_type='application/json')
	{
		return $this->setBody($body)
				->setContentType($content_type)
				->setStatus($status);
	}

	public function setCsv($body, $status=200, $content_type='text/csv')
	{
		return $this->setBody($body)
				->setContentType($content_type)
				->setStatus($status);
	}

	public function append($body)
	{
		$this->body .= $body;
		return $this;
	}

	public function prepend($body)
	{
		$this->body = $body . $this->body;
		return $this;
	}

	public function getHeaders()
	{
		return $this->headers;
	}

	public function setHeaders($headers)
	{
		//return $this->headers = $headers;
		$this->headers = [];
		foreach ($headers as $k => $v) {
			if (is_numeric($k)) {
				if (strpos($v, ':') === false) {
					continue;
				}
				$parts = explode(':', $v);
				$k = trim($parts[0]);
				$v = trim($parts[1]);
			}
			$this->addHeader($k, $v);
		}
		return $this;
	}

	public function addHeader($key, $value)
	{
		$key = ucwords(strtolower($key), " -\t\r\n\f\v");
		$this->headers[$key] = $value;
		return $this;
	}


	public function sendHeaders()
	{
		if (App::isCli()) {
			return;
		}

		if ($this->headers_sent) {
			//error_log("Warning: headers already sent");
			return;

		} else if (headers_sent()) {
			error_log("Warning: headers already sent");
			$this->headers_sent = true;
			return;
		}

		if (! empty($this->status)) {
			$reasonPhrase = empty($this->reasonPhrase) ? "Unknown http status" : trim($this->reasonPhrase);
			$this->headers['X-Status'] = $this->status . ' ' . $reasonPhrase;
			
			header('HTTP/1.0 ' . $this->status . ' ' . $reasonPhrase);
		}

		if (empty($this->headers['Content-Type']) && ! empty($this->content_type)) {
			$this->headers['Content-Type'] = $this->content_type;
		}

		if (empty($this->headers['Content-Length'])) {
			$this->headers['Content-Length'] = $this->getContentLength();
		}

		foreach ($this->headers as $k => $v) {
			header($k . ": " . $v);
		}

		$this->headers_sent = true;

		return $this;
	}


	public function send()
	{
		$this->headers_sent = ($this->headers_sent || headers_sent());

		if (! $this->headers_sent) {
			$this->sendHeaders();
		}

		if (strlen($this->body) > 0) {
			echo $this->body;
		}

		return $this;
	}

}
