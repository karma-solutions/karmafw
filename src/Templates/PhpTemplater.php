<?php

namespace KarmaFW\Templates;


class PhpTemplater
{
	protected $variables = [];
	protected $layout = null;
	protected $plugins = [];
	public $templates_dirs = [];


	public function __construct($templates_dirs=null, $variables=[], $layout=null)
	{
		$this->setTemplateDirs($templates_dirs);

		$this->variables = $variables;
		$this->layout = $layout;
	}


	public function setVariables($variables=[])
	{
		$this->variables = $variables;
	}

	public function getVariables()	{
		return $this->variables;
	}

	public function setAllVariables($var_name, $var_value)
	{
		$this->variables[$var_name] = $var_value;
	}

	public function setTemplateDirs($templates_dirs=[])
	{
		if (empty($templates_dirs) && defined('TPL_DIR')) {
			$templates_dirs = explode(':', TPL_DIR);

		} else if (is_string($templates_dirs)) {
			$templates_dirs = explode(':', $templates_dirs);

		} else if (is_array($templates_dirs)) {

		} else {
			$templates_dirs = [];
		}

		$this->templates_dirs = $templates_dirs;
	}


	public function assign($var_name, $var_value=null)
	{
		if (is_array($var_name)) {
			foreach ($var_name as $k => $v) {
				$this->assign($k, $v);
			}
			return $this;
		}

		$this->variables[$var_name] = $var_value;

		return $this;
	}



	function tpl($tpl_path=null, $variables=[], $layout=null, $templates_dirs=[])
	{
		$variables = array_merge($this->variables, (is_array($variables) ? $variables : []) );
		$templates_dirs = array_merge($this->templates_dirs, (is_array($templates_dirs) ? $templates_dirs : []) );

		return new PhpTemplate($tpl_path, $variables, $layout, $templates_dirs);
	}

}
