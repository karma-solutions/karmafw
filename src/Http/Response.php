<?php

namespace KarmaFW\Http;

use \KarmaFW\App\Tools;


class Response
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
	protected $redirect_url = null;
	protected $download_file_name = null;
	protected $download_file_path = null;
	protected $attributes = [];


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
		return empty($this->body) ? 0 : strlen($this->body);
		//return empty($this->body) || ! is_string($this->body) ? 0 : strlen($this->body);
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

	public function html($body, $status=200, $content_type='text/html; charset=utf8')
	{
		if (! is_null($status)) {
			$this->setStatus($status);
		}
		if (! is_null($content_type)) {
			$this->setContentType($content_type);
		}
		return $this->setBody($body);
	}
	
	public function json($json, $download_file_name=null, $status=200, $content_type='application/json; charset=utf8', $add_content_disposition=true)
	{
		if (! is_string($json) || ! in_array(substr($json, 0, 1), ['"', "'", '[', '{'])) {
			$json = json_encode($json);
		}
		//return $this->download($json, $download_file_name, $status, $content_type);
		
		$this->download_file_name = $download_file_name;

		if ($add_content_disposition && !empty($this->download_file_name)) {
			$this->headers['Content-disposition'] = 'attachment; filename="' . basename($this->download_file_name) . '"';
		}

		return $this->setBody($json)
				->setContentType($content_type)
				->setStatus($status);
	}

	public function csv(array $rows, $download_file_name=null, $status=200, $content_type='text/csv; charset=utf8', $add_content_disposition=true)
	{
		if (is_array($rows)) {
			// transform array to csv
			$body = get_csv($rows);
		} else {
			$body = "";
		}
		//return $this->download($json, $download_file_name, $status, $content_type);
		
		$this->download_file_name = $download_file_name;

		if ($add_content_disposition && !empty($this->download_file_name)) {
			$this->headers['Content-disposition'] = 'attachment; filename="' . basename($this->download_file_name) . '"';
		}

		return $this->setBody($body)
				->setContentType($content_type)
				->setStatus($status);
	}
	
	public function download($file_path, $download_file_name=null, $status=200, $content_type='application/octet-stream', $add_content_disposition=true)
	{
		$this->download_file_path = $file_path;
		$this->download_file_name = empty($download_file_name) ? basename($file_path) : $download_file_name;

		if ($add_content_disposition && !empty($this->download_file_name)) {
			$this->headers['Content-disposition'] = 'attachment; filename="' . basename($this->download_file_name) . '"';
		}

		return $this->setBody('')
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
		if (Tools::isCli()) {
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

		if ($this->status === 200 && empty($this->body)) {
			// No content
			//$this->setStatus(204);
		}

		if (! empty($this->status)) {
			$reasonPhrase = empty($this->reasonPhrase) ? "Unknown http status" : trim($this->reasonPhrase);
			$this->headers['X-Status'] = $this->status . ' ' . $reasonPhrase;
			
			header('HTTP/1.0 ' . $this->status . ' ' . $reasonPhrase);
		}


		if ($this->download_file_name) {
			// Download

			$content_type = $this->getContentType();

			if ($this->download_file_path) {
				// DOWNLOAD A LOCAL FILE

				if (! is_file($this->download_file_path)) {
					// File not found
					return $this->html("File not found", 404);

				} else {
					// File exists
					if (empty($content_type)) {
						$content_type = mime_content_type($this->download_file_path);
					}

					$this->headers['Content-Length'] = filesize($this->download_file_path);

					$this->body = '';
				}

			} else {
				// DOWNLOAD A VIRTUAL FILE
				$this->headers['Content-Length'] = strlen($this->body);
			}


			if (empty($content_type)) {
				$content_type = "application/octet-stream";
			}
			$this->setContentType($content_type);

			$this->headers['Content-Transfer-Encoding'] = "Binary";
			//$this->headers['Content-disposition'] = 'attachment; filename="' . basename($this->download_file_name) . '"';
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

		if ($this->download_file_name) {
			// Download

			if ($this->download_file_path) {
				// Download a local file
				if (is_file($this->download_file_path)) {
					readfile($this->download_file_path);

				} else {
					// 404
					echo "Error 404: file not found";
				}

			} else {
				// Download a virtual file

			}

		} else {
			// Echo HTML
		}


		// Echo HTML or Virtual file
		if (strlen($this->body) > 0) {
			echo $this->body;

		} else {
			// No content
			// TODO: renvoyer code 204 ?
		}

		return $this;
	}


	public function redirect($redirect_url, $status=302)
	{
		$this->redirect_url = $redirect_url;

		$this->addHeader('Location', $redirect_url)
			->setStatus($status)
			->setBody('');

		return $this;
	}


	public function error($status=500, $body='Error', $content_type='text/html; charset=utf8')
	{
		$this->setStatus($status)
			->setContentType($content_type)
			->setBody($body);

		return $this;
	}


	public function error401($body='Unauthorized', $content_type='text/html; charset=utf8')
	{
		return $this->error(401, $body, $content_type);
	}

	public function error403($body='Forbidden', $content_type='text/html; charset=utf8')
	{
		return $this->error(403, $body, $content_type);
	}

	public function error404($body='Not Found', $content_type='text/html; charset=utf8')
	{
		return $this->error(404, $body, $content_type);
	}

	public function error500($body='Internal Server Error', $content_type='text/html; charset=utf8')
	{
		return $this->error(500, $body, $content_type);
	}

	public function error503($body='Service Unavailable', $content_type='text/html; charset=utf8')
	{
		return $this->error(503, $body, $content_type);
	}


	public function getAttributes()
	{
		return $this->attributes;
	}

	public function setAttributes($attributes)
	{
		$this->attributes = $attributes;
	}

	public function getAttribute($key, $default_value=null)
	{
		return isset($this->attributes[$key]) ? $this->attributes[$key] : $default_value;
	}

	public function setAttribute($key, $value)
	{
		$this->attributes[$key] = $value;
	}

}
