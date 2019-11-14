<?php

namespace KarmaFW\Templates;


class PhpTemplate
{
	public $tpl_dir = APP_DIR . '/templates';
	protected $vars = [];
	protected $plugins = [];
	protected $layout = null;

	function __construct($tpl_dir=null, $default_vars=[])
	{
		if (is_null($tpl_dir) && defined('TPL_DIR')) {
			$tpl_dir = TPL_DIR;
		}
		
		if (! is_null($tpl_dir)) {
			$this->tpl_dir = $tpl_dir;
		}

		$this->assign($default_vars);

		$template = $this;
		$this->addPlugin('layout', function ($param) use ($template) {
			$template->layout = $param;
			return '';
		});
		$this->addPlugin('\/\/', function ($param) {
			return '';
		});
		$this->addPlugin('tr', function ($param) {
			return gettext($param);
		});
		$this->addPlugin('include', function ($param) use ($template) {
			$template = new PhpTemplate($template->tpl_dir, $template->vars);
			$templatechild_content = $template->fetch($param);
			return $templatechild_content;
		});
		$this->addPlugin('routeUrl', function ($param) use ($template) {
			$params = explode(' ', $param);
			$route_name = array_shift($params);
			$url_args = $params;
			$url = getRouteUrl($route_name, $url_args);
			return $url;
		});
	}

	public static function createTemplate($tpl_dir=null, $default_vars=[])
	{
		return new PhpTemplate($tpl_dir, $default_vars);
	}

	public function fetch($tpl, $layout=null, $extra_vars=[])
	{
		$tpl_dirs = [];

		if (! is_null($this->tpl_dir) && is_dir($this->tpl_dir)) {
			$tpl_dirs[] = $this->tpl_dir; // user templates
		}

		if (is_dir(FW_DIR . '/templates')) {
			$tpl_dirs[] = FW_DIR . '/templates'; // framework templates
		}

		if (empty($tpl_dirs)) {
			throw new \Exception("No Templates dir. Please define TPL_DIR with a valid directory path.", 1);
		}

		$tpl_path = false;
		foreach ($tpl_dirs as $tpl_dir) {
			$tpl_path = $tpl_dir . '/' . $tpl;

			if (is_file($tpl_path)) {
				break;
			}

			$tpl_path = null;
		}

		if (is_null($tpl_path)) {
			throw new \Exception("Template not found : " . $tpl, 1);
		}
		
		$tpl_vars = array_merge($this->vars, $extra_vars);
		extract($tpl_vars);
		
		if ($tpl_path) {
			ob_start();
			include($tpl_path);
			$content = ob_get_contents();
			ob_end_clean();

		} else {
			$content = '';
		}


		// plugins. ex: {tr English text} ==> "Texte francais"
		if (! empty($this->plugins)) {
			foreach ($this->plugins as $prefix => $callback) {
				//preg_match_all('/{' . $prefix . ':([^}]+)}/', $content, $regs, PREG_SET_ORDER);
				preg_match_all('/{' . $prefix . ' ([^}]+)}/', $content, $regs, PREG_SET_ORDER);
				foreach($regs as $reg) {
					$replaced = $callback($reg[1]);
					$content = str_replace($reg[0], $replaced, $content);
				}

			}
		}

		// variables. ex: {$user_name} ==> John
		if (true) {
			preg_match_all('/{\$([a-zA-Z0-9_\[\]\']+)}/', $content, $regs, PREG_SET_ORDER);
			foreach($regs as $reg) {
				$var = $reg[1];
				
				if (isset(${$var})) {
					$replaced = ${$var};
					$content = str_replace($reg[0], $replaced, $content);
				} else {
					//$content = str_replace($reg[0], '', $content);
				}
			}
		}

		// si pas de layout defini, on recupere celui eventuel du plugin layout (c'est a dire venant d'un marker {layout xxx} dans le template)
		if (is_null($layout)) {
			$layout = $this->layout;
		}
		$this->layout = null;

		if (empty($layout)) {
			return $content;

		} else {
			$extra_vars['child_content'] = $content;
			//$extra_vars['child_content'] = '{CONTENT OF ' . $tpl . '}';
			$content_layout = $this->fetch($layout, null, $extra_vars);
			return $content_layout;
		}
	}

	public function display($tpl, $layout=null, $extra_vars=[])
	{
		echo $this->fetch($tpl, $layout, $extra_vars);
	}

	public function assign($var_name, $var_value=null)
	{
		if (is_array($var_name)) {
			foreach ($var_name as $k => $v) {
				$this->assign($k, $v);
			}
			return $this;
		}

		$this->vars[$var_name] = $var_value;

		return $this;
	}


	public function addPlugin($prefix, $callback)
	{
		$this->plugins[$prefix] = $callback;
	}

}
