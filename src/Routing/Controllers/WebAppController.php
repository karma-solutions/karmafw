<?php

namespace KarmaFW\Routing\Controllers;

use \KarmaFW\WebApp;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;
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

	
	public function __construct(Request $request, Response $response)
	{
		parent::__construct($request, $response);

		$this->request = $request;
		$this->response = $response;

		$this->request_uri = $request->SERVER['REQUEST_URI'];
		$this->request_method = $request->SERVER['REQUEST_METHOD'];
		$this->route = $request->getRoute();
		

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




	protected function showError($http_status = 500, $meta_title = 'Server Error', $h1 = 'Error 500 - Server Error', $message = 'an error has occured')
	{
		if ($template = $this->getTemplate()) {
			$template->assign('meta_title', $meta_title);
			$template->assign('h1', $h1);
			$template->assign('p', $message);
			$template->assign('http_status', $http_status);

			$error_template = 'error.tpl.php';
			if (defined('ERROR_TEMPLATE')) {
				$error_template = ERROR_TEMPLATE;
			}

			//$template->display($error_template);
			return $this->response->html( $template->fetch($error_template) , $http_status);

		} else {
			//header("HTTP/1.0 " . $http_status . " " . $meta_title);

			$output_html = '';
			$output_html .= '<html>' . PHP_EOL;
			$output_html .= '<head>' . PHP_EOL;
			if (! empty($meta_title)) {
				$output_html .= '<title>' . $meta_title . '</title>' . PHP_EOL;
			}
			$output_html .= '</head>' . PHP_EOL;
			$output_html .= '<body>' . PHP_EOL;
			if (! empty($h1)) {
				$output_html .= '<h1>' . $h1 . '</h1>' . PHP_EOL;
			}
			if (! empty($message)) {
				$output_html .= '<p>' . $message . '</p>' . PHP_EOL;
			}
			$output_html .= '</body>' . PHP_EOL;
			$output_html .= '</html>' . PHP_EOL;

			//echo $output_html;

			return $this->response->html($output_html, $http_status);
		}

	}

	protected function showError400($title = 'Bad request', $message = '')
	{
		return $this->showError(400, $title, $title, $message);
	}

	protected function showError403($title = 'Forbidden', $message = 'you are not allowed')
	{
		return $this->showError(403, $title, $title, $message);
	}

	protected function showError404($title = 'Page not found', $message = "The page you're looking for doesn't exist")
	{
		return $this->showError(404, $title, $title, $message);
	}

	protected function showError500($title = 'Internal Server Error', $message = 'An error has occured')
	{
		return $this->showError(500, $title, $title, $message);
	}

	protected function showError503($title = 'Service Unavailable', $message = 'The service is unavailable')
	{
		return $this->showError(503, $title, $title, $message);
	}

}
