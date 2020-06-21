<?php

namespace KarmaFW\Routing\Controllers;

use \KarmaFW\WebApp;


class WebAppController extends AppController
{
	protected $request_uri = null;
	protected $request_method = null;
	protected $route = null;
	protected $template;

	
	public function __construct($request_uri=null, $request_method=null, $route=null)
	{
		parent::__construct($request_uri, $request_method, $route);

		$this->user_id = session('user_id');

		$_SESSION['flash'] = []; // ['success' => 'action done !', 'error' => 'an error occured', 'warning' => 'notice ...']

		if (defined('TPL_DIR')) {
			$this->template = WebApp::createTemplate();

			$this->template->assign('user_id', $this->user_id);

			if (defined('APP_NAME')) {
				$this->template->assign('meta_title', APP_NAME);
				$this->template->assign('meta_description', APP_NAME);
				$this->template->assign('h1', APP_NAME);
			}
		}
	}

	public function getRoute()
	{
		return $this->route;
	}

	public function getRequestMethod()
	{
		return $this->request_method;
	}

	public function getRequestUri()
	{
		return $this->request_uri;
	}

	public function getTemplate()
	{
		return $this->template;
	}




	public function error400()
	{
		$meta_title = 'Bad request';
		$h1 = 'Error 400 - Bad request';
		$content = '';
		return $this->error(400, $meta_title, $h1, $content);
	}

	public function error403()
	{
		$meta_title = 'Forbidden';
		$h1 = 'Error 403 - Forbidden';
		$content = 'you are not allowed';
		return $this->error(403, $meta_title, $h1, $content);
	}

	public function error404()
	{
		$meta_title = 'Page not found';
		$h1 = 'Error 404 - Page not found';
		$content = "The page you're looking for doesn't exist.";
		return $this->error(404, $meta_title, $h1, $content);
	}

	public function error500()
	{
		$meta_title = 'Internal Server Error';
		$h1 = 'Error 500 - Internal Server Error';
		$content = 'An error has occured';
		return $this->error(500, $meta_title, $h1, $content);
	}

	public function error503()
	{
		$meta_title = 'Service Unavailable';
		$h1 = 'Error 503 Service Unavailable';
		$content = 'The service is unavailable';
		return $this->error(503, $meta_title, $h1, $content);
	}


}
