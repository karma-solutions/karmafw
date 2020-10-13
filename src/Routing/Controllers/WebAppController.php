<?php

namespace KarmaFW\Routing\Controllers;

use \KarmaFW\WebApp;
use \KarmaFW\Lib\Hooks\HooksManager;


class WebAppController extends AppController
{
	protected $request;
	protected $response;
	
	protected $request_uri = null;
	protected $request_method = null;
	protected $route = null;
	protected $template;
	protected $user_id;
	protected $flash;

	
	public function __construct($request, $response, $route=null)
	{
		parent::__construct();

		$this->request = $request;
		$this->response = $response;

		$this->request_uri = $request->SERVER['REQUEST_URI'];
		$this->request_method = $request->SERVER['REQUEST_METHOD'];
		$this->route = $route;

		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('webcontroller.before', [$this]);
		}

		$this->user_id = session('user_id');

		$this->flash = session('flash');
		$_SESSION['flash'] = []; // ['success' => 'action done !', 'error' => 'an error occured', 'warning' => 'notice ...']

		if (defined('TPL_DIR')) {
			$this->template = WebApp::createTemplate();

			$this->template->assign('user_id', $this->user_id);
			$this->template->assign('flash', $this->flash);

			if (defined('APP_NAME')) {
				$this->template->assign('meta_title', APP_NAME);
				$this->template->assign('meta_description', APP_NAME);
				$this->template->assign('h1', APP_NAME);
			}
		}

		if (defined('USE_HOOKS') && USE_HOOKS) {
			HooksManager::applyHook('webcontroller.after', [$this]);
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




	public function error($http_status, $meta_title=null, $h1=null, $message=null)
	{
		return WebApp::error($http_status, $meta_title, $h1, $message);
	}

	public function error400($title = 'Bad request', $message = '')
	{
		return $this->error(400, $title, $title, $message);
	}

	public function error403($title = 'Forbidden', $message = 'you are not allowed')
	{
		return $this->error(403, $title, $title, $message);
	}

	public function error404($title = 'Page not found', $message = "The page you're looking for doesn't exist")
	{
		return $this->error(404, $title, $title, $message);
	}

	public function error500($title = 'Internal Server Error', $message = 'An error has occured')
	{
		return $this->error(500, $title, $title, $message);
	}

	public function error503($title = 'Service Unavailable', $message = 'The service is unavailable')
	{
		return $this->error(503, $title, $title, $message);
	}


}
