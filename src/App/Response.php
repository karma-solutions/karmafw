<?php

namespace KarmaFW\App;

// TODO: a remplacer par ou rendre compatible avec GuzzleHttp\Psr7\Response


class Response
{
	protected $headers = [];
	protected $body = '';
	protected $status = 200;
	protected $reasonPhrase = 'OK';
	protected $content_type = '';
	protected $headers_sent = false;
	protected $protocol = null;

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


	public function __construct($status=200, array $headers=[], $body=null, $version='1.1', $reason=null) // Psr7
	{
		$this->body = $body;
		$this->setStatus($status);
		$this->setheaders($headers);

        $this->protocol = $version;
	}
	//public ResponseText::__construct($body=null, $content_type='text/plain', $status=200) { return Response($status, ['Content-Type' => $content_type], $body); }
	//public ResponseHtml::__construct($body=null, $status=200) { return ResponseText($body, 'html', $status); }


	public function getStatus()
	{
		return $this->status;
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
	}

	public function getContentType()
	{
		return $this->content_type;
	}

	public function setContentType($content_type)
	{
		$this->content_type = $content_type;
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

	public function setContent($body)
	{
		// DEPRECATED
		return $this->setBody($body);
	}

	public function setBody($body)
	{
		$this->body = $body;
	}

	public function append($body)
	{
		$this->body .= $body;
	}

	public function prepend($body)
	{
		$this->body = $body . $this->body;
	}

	public function getHeaders()
	{
		return $this->headers;
	}

	public function setheaders($headers)
	{
		//return $this->headers = $headers;
		$this->headers = [];
		foreach ($headers as $k => $v) {
			$this->addHeader($k, $v);
		}
	}

	public function addHeader($key, $value)
	{
		$key = ucwords(strtolower($key), " -\t\r\n\f\v");
		$this->headers[$key] = $value;
	}


	public function sendHeaders()
	{
		if ($this->headers_sent) {
			//error_log("Warning: headers already sent");
			return;

		} else if (headers_sent()) {
			error_log("Warning: headers already sent");
			$this->headers_sent = true;
			return;
		}

		if (! empty($this->status)) {
			$reasonPhrase = empty($this->reasonPhrase) ? "Unknown http status" : $this->reasonPhrase;
			header('HTTP/1.0 ' . $this->status . ' ' . $reasonPhrase);
			$this->headers['X-Status'] = $this->status . ' ' . $reasonPhrase;
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
	}

}
