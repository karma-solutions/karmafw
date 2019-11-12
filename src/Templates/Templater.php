<?php

namespace KarmaFW\Templates;


class Templater
{
	public $tpl_dir = APP_DIR . '/templates';
	protected $vars = [];
	protected $plugins = [];

	function __construct($tpl_dir=null, $default_vars=[])
	{
		if (is_null($tpl_dir) && defined('TPL_DIR')) {
			$tpl_dir = TPL_DIR;
		}
		
		if (! is_null($tpl_dir)) {
			$this->tpl_dir = $tpl_dir;
		}

		$this->assign($default_vars);

		$this->addPlugin('tr', function ($param) {
			return gettext($param);
		});
	}

	public function fetch($tpl, $layout=null, $extra_vars=array())
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
		
		extract($this->vars);
		extract($extra_vars);
		
		if ($tpl_path) {
			ob_start();
			include($tpl_path);
			$content = ob_get_contents();
			ob_end_clean();

		} else {
			$content = '';
		}


		// plugins. ex: {tr:English text} ==> "Texte francais"
		if (! empty($this->plugins)) {
			foreach ($this->plugins as $prefix => $callback) {
				preg_match_all('/{' . $prefix . ':([^}]+)}/', $content, $regs, PREG_SET_ORDER);
				foreach($regs as $reg) {
					$replaced = $callback($reg[1]);
					$content = str_replace($reg[0], $replaced, $content);
				}

			}
		}


		if (empty($layout)) {
			return $content;

		} else {
			$content_layout = $this->fetch($layout, null, array('layout_content' => $content));
			return $content_layout;
		}
	}

	public function display($tpl, $layout=null)
	{
		echo $this->fetch($tpl, $layout);
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
