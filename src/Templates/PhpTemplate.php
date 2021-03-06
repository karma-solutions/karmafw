<?php

namespace KarmaFW\Templates;

use \KarmaFW\App;


class PhpTemplate
{
	protected $tpl_path = null;
	protected $variables = [];
	protected $layout = null;
	protected $plugins = [];
	public $templates_dirs = APP_DIR . '/templates';


	public static function createTemplate($tpl_path=null, $variables=[], $layout=null, $templates_dirs=null)
	{
		return new PhpTemplate($tpl_path, $variables, $layout, $templates_dirs);
	}


	function __construct($tpl_path=null, $variables=[], $layout=null, $templates_dirs=null)
	{
		if (is_null($templates_dirs) && defined('TPL_DIR')) {
			$templates_dirs = explode(':', TPL_DIR);

		} else if (is_string($templates_dirs)) {
			$templates_dirs = explode(':', $templates_dirs);

		} else if (is_array($templates_dirs)) {

		} else {
			$templates_dirs = null;
		}
		
		$this->tpl_path = $tpl_path;
		$this->variables = is_array($variables) ? $variables : [];
		$this->layout = $layout;
		$this->templates_dirs = $templates_dirs;


		// PLUGINS

		$template = $this;
		$this->addPlugin('layout', function ($key) use ($template) {
			// {layout my_layout_template.tpl.php}
			$template->layout = $key;
			return '';
		});
		$this->addPlugin('\/\/', function ($key) {
			// {// this is a comment}
			return '';
		});
		$this->addPlugin('#', function ($key) {
			// {# this is a comment}
			return '';
		});
		$this->addPlugin('assign', function ($key, $value) use ($template) {
			// {assign var_name content of my variable}
			$template->assign($key, $value);
			return '';
		});
		$this->addPlugin('tr', function ($key) {
			// {tr my text in english} ==> mon texte en francais
			return gettext($key);
		});
		$this->addPlugin('include', function ($key) use ($template) {
			// {include my_template.tpl.php}
			$template = new PhpTemplate($template->templates_dirs, $template->variables, null, $template->templates_dirs);
			$templatechild_content = $template->fetch($key);
			return $templatechild_content;
		});
		$this->addPlugin('routeUrl', function ($key, $value=[]) {
			// {routeUrl login_page} ===> /login
			$route_name = $key;
			$url_args = explode(' ', $value);
			$url = getRouteUrl($route_name, $url_args);
			return $url;
		});

		$this->addPlugin('block', function ($key, $matched_expr, $begin_block_offset_start, &$content) use ($template) {
			// {block block_name}my html content{/block}  ==> assign variable $block_name with block content
			$begin_block_offset_end = $begin_block_offset_start + strlen($matched_expr);

			$end_block_offset_start = strpos($content, '{/block}', $begin_block_offset_end);

			if ($end_block_offset_start) {
				$block = isset($template->variables[$key]) ? $template->variables[$key] : '';

				$block = substr($content, $begin_block_offset_end, $end_block_offset_start - $begin_block_offset_end) . $block;

				$template->assign($key, $block);

				$end_block_offset_end = $end_block_offset_start + strlen("{/block}");
				$content = substr($content, 0, $begin_block_offset_start) . substr($content, $end_block_offset_end);
			}


			return '';
		});
	}


	public function fetch($tpl=null, $extra_vars=[], $layout=null, $options=[])
	{
		$tpl_dirs = [];
		$ts_start = microtime(true);

		// user templates
		if (! empty($this->templates_dirs)) {
			foreach ($this->templates_dirs as $templates_dir) {
				if (is_dir($templates_dir)) {
					$tpl_dirs[] = $templates_dir;
				}
			}
		}

		// framework templates
		if (is_dir(FW_DIR . '/templates')) {
			$tpl_dirs[] = FW_DIR . '/templates';
		}

		if (empty($tpl_dirs)) {
			throw new \Exception("No Templates dir. Please define TPL_DIR with a valid directory path.", 1);
		}

		if (empty($tpl)) {
			$tpl = $this->tpl_path;
		}
		if (empty($tpl)) {
			//throw new Exception("no template specified", 1);
			return '';
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
			throw new \Exception("Template not found : " . $tpl . " (dirs: " . implode(" | ", $tpl_dirs) . ")", 1);
		}
		
		//$tpl_vars = array_merge($this->variables, $extra_vars);
		//extract($tpl_vars);
		if (! empty($extra_vars) && is_array($extra_vars)) {
			$this->variables = array_merge($this->variables, $extra_vars);
		}
		extract($this->variables);
		
		if ($tpl_path) {
			ob_start();
			include($tpl_path);
			$content = ob_get_contents();
			ob_end_clean();

		} else {
			$content = '';
		}


		// TODO: voir comment bien injecter cette dependance
		$debugbar = App::getData('debugbar');
		if ($debugbar) {
			
			if (isset($debugbar['templates'])) {
				//$debugbar['templates']->info($tpl);

				$debugbar_message_idx = $debugbar['templates']->addMessage(['tpl' => $tpl]);
			}
		}


		// plugins. ex: {tr English text} ==> "Texte francais"
		if (empty($options['no_plugins'])) {
			if (! empty($this->plugins)) {
				foreach ($this->plugins as $prefix => $callback) {

					if ($prefix != 'block') {
						preg_match_all('/{' . $prefix . ' ([^} ]+)( ([^}]+))?}/', $content, $regs, PREG_SET_ORDER);
						foreach($regs as $reg) {
							$value = isset($reg[3]) ? $reg[3] : null;
							$replaced = $callback($reg[1], $value);
							$content = str_replace($reg[0], $replaced, $content);
						}
					} else {

						$nb_iterations = 10;
						while ($nb_iterations--) {
							preg_match('/{' . $prefix . ' ([^}]+)}/', $content, $regs, PREG_OFFSET_CAPTURE);
							if (! $regs) {
								break;
							}

							$replaced = $callback($regs[1][0], $regs[0][0], $regs[0][1], $content);
							$content = str_replace($regs[0][0], $replaced, $content);
						}
					}


				}
			}

			// variables. ex: {$user_name} ==> John
			if (true) {
				preg_match_all('/{\$([a-zA-Z0-9._\[\]\']+)}/', $content, $regs, PREG_SET_ORDER);
				foreach($regs as $reg) {
					$var = $reg[1];
					$var_parts = explode(".", $var);
					
					if (count($var_parts) > 1) {
						// $variable.key  ==> $variable['key']

						$var = ${ array_shift($var_parts) };
						foreach ($var_parts as $part) {
							$var = $var[ $part ];
						}

						$replaced = $var;
						$content = str_replace($reg[0], $replaced, $content);

					} else if (isset(${$var})) {
						// $variable
						$replaced = ${$var};
						$content = str_replace($reg[0], $replaced, $content);

					} else {
						// if variable not exists, replace with empty string
						$content = str_replace($reg[0], '', $content);
					}
				}
			}
		}

		// si pas de layout defini, on recupere celui eventuel du plugin layout (c'est a dire venant d'un marker {layout xxx} dans le template)
		if (is_null($layout)) {
			$layout = $this->layout;
		}
		$layout_old = $this->layout;
		$this->layout = null;


		$ts_end = microtime(true);
		$duration = $ts_end - $ts_start;

		if (isset($debugbar_message_idx) && ! is_null($debugbar_message_idx)) {

			$debugbar['templates']->updateMessage($debugbar_message_idx, [
				'tpl' => $tpl,
				'layout' => $layout_old,
				'content_length' => strlen($content),
				'content_length_str' => formatSize(strlen($content)),
				'duration' => $duration,
				'duration_str' => formatDuration($duration),
				'vars' => $this->variables,
			]);
		}


		if (empty($layout)) {
			return $content;

		} else {
			$extra_vars['child_content'] = $content;
			//$extra_vars['child_content'] = '{CONTENT OF ' . $tpl . '}';
			$content_layout = $this->fetch($layout, $extra_vars, null, $options);
			return $content_layout;
		}
	}

	public function display($tpl=null, $extra_vars=[], $layout=null, $options=[])
	{
		echo $this->fetch($tpl, $extra_vars, $layout, $options);
	}


	public function setAllVariables($variables=[])
	{
		$this->variables = $variables;
	}

	public function setVariable($var_name, $var_value)
	{
		$this->variables[$var_name] = $var_value;
	}

	public function getTplPath()
	{
		return $this->tpl_path;
	}

	public function setTplPath($tpl_path)
	{
		$this->tpl_path = $tpl_path;
		return $this;
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


	public function addPlugin($prefix, $callback)
	{
		$this->plugins[$prefix] = $callback;
	}

}
