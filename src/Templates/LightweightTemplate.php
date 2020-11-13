<?php

namespace KarmaFW\Templates;

use \KarmaFW\App;


class LightweightTemplate {
	// https://codeshack.io/lightweight-template-engine-php/

	static $blocks = array();
	static $cache_path = TPL_CACHE_DIR; // APP_DIR . '/var/cache/templates';
	static $tpl_path = TPL_DIR;
	static $cache_enabled = (ENV == 'prod') || true;
	static $tpl_last_updated = null;


	protected $data = [];

	public function __construct($tpl_path=null, $variables=[], $layout=null) 
	{
		$this->data = $variables;
	}

	public function assign($k, $v=null) 
	{
		if (is_array($k)) {
			$keys = $k;
			foreach ($keys as $k => $v) {
				$this->assign($k, $v);
			}

		} else {
			$this->data[$k] = $v;
		}
	}
	
	public function getVariables() 
	{
		return $this->data;
	}
	
	public function getVar($var_name, $default_value=null) 
	{
		return isset($this->data[$var_name]) ? $this->data[$var_name] : $default_value;
	}

	public function fetch($tpl=null, $extra_vars=[], $layout=null, $options=[]) 
	{
		ob_start();
		$this->display($tpl, $extra_vars, $layout, $options);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public function display($tpl=null, $extra_vars=[], $layout=null, $options=[]) 
	{
		$tpl_data = $this->data + $extra_vars;
		self::view($tpl, $tpl_data);
		return true;
	}

	
	public static function view($file, $tpl_data = array()) {
		$cached_file = self::cache($file);
	    extract($tpl_data, EXTR_SKIP);

		$debugbar = App::getData('debugbar');
		if ($debugbar) {
			if (isset($debugbar['templates_vars'])) {
				$debugbar['templates_vars']->setData($tpl_data);
			}
		}
		unset($debugbar);

	   	require $cached_file;
	}


	protected static function cache($file) {
		if (!file_exists(self::$cache_path)) {
		  	if (! @mkdir(self::$cache_path, 0744)) {
		  		throw new \Exception("Cannot create templates cache dir " . self::$cache_path, 1);
		  	}
		}

	    $cached_file = self::$cache_path . '/' . str_replace(array('/', '.html'), array('_', ''), $file . '.php');
	    $cached_file_exists = is_file($cached_file);

	    if ($cached_file_exists) {
		    $cached_file_updated = filemtime($cached_file);
		    $file_path = strpos($file, '/') === 0 ? $file : (self::$tpl_path . '/' . $file);
		    self::$tpl_last_updated = filemtime($file_path);
	    } else {
	    	$cached_file_updated = null;
	    }

	    if (ENV == 'dev') {
	    	// on force le parcours de tous les fichiers inclus pour avoir la vraie valeur de self::$tpl_last_updated
	    	$code = self::includeFiles($file);
	    }

	    if (!self::$cache_enabled || ! $cached_file_exists || $cached_file_updated < self::$tpl_last_updated) {
	    	if (! isset($code)) {
				$code = self::includeFiles($file);
	    	}
			$code = self::compileCode($code);
	        file_put_contents($cached_file, '<?php class_exists(\'' . __CLASS__ . '\') or exit; ?>' . PHP_EOL . $code);

	    } else {
	    	//header('X-Template: cached'); // TODO: $response->addHeader(...)

			$debugbar = App::getData('debugbar');
			if ($debugbar) {
				if (isset($debugbar['templates'])) {
					$debugbar_message_idx = $debugbar['templates']->addMessage([
						'tpl' => $cached_file,
						'content_length' => filesize($cached_file),
						'content_length_str' => formatSize(filesize($cached_file)),
						'cached' => true,
					]);
				}
			}

	    }
		return $cached_file;
	}

	public static function clearCache() {
		foreach(glob(self::$cache_path . '/*') as $file) {
			unlink($file);
		}
	}

	protected static function compileCode($code) {
		$code = self::compileBlock($code);
		$code = self::compileYield($code);

		$code = self::compileModules($code);
		$code = self::compileEscapedEchos($code);
		$code = self::compileEchos($code);
		//$code = self::compilePHP($code);
		return $code;
	}

	protected static function includeFiles($file, $level=0, $caller_file=null, $parent_file=null) {
		$file_path = strpos($file, '/') === 0 ? $file : (self::$tpl_path . '/' . $file);

		if (! is_file($file_path)) {
			throw new \Exception("Template file not found " . $file, 500);
		}
		
		$code = file_get_contents($file_path);
		$code_init = $code;
		$layout = null;

		$ts_update_file = filectime($file_path);
		if (empty(self::$tpl_last_updated) || self::$tpl_last_updated < $ts_update_file) {
			self::$tpl_last_updated = $ts_update_file;
		}

		static $tpl_idx = null;
		if (is_null($tpl_idx) || (empty($caller_file) && empty($parent_file))) {
			$tpl_idx = 0;
		}
		$tpl_idx++;

		$debugbar = App::getData('debugbar');
		if ($debugbar) {
			if (isset($debugbar['templates'])) {
				$debugbar_message_idx = $debugbar['templates']->addMessage([
					'tpl' => $file,
				]);
			}
		}

		$ts_start = microtime(true);


		// Layout (1)
		preg_match_all('/{layout ?\'?(.*?)\'? ?}/i', $code, $layout_matches, PREG_SET_ORDER);
		if ($layout_matches) {
			$value = $layout_matches[0];
			$layout = $value[1];
		} else {
			$layout = null;
		}


		if (defined('ENV') && ENV == 'dev') {
			$tpl_infos = '';
			if ($caller_file) {
				$tpl_infos .= ' layout for ' . $caller_file . '';
			}
			if ($parent_file) {
				$tpl_infos .= ' child of ' . $parent_file . '';
			}
			if ($layout) {
				$tpl_infos .= ' with layout ' . $layout . '';
			}

			$begin = '<!-- [' . $level . '] BEGIN TEMPLATE #' . $tpl_idx . ' : ' . $file . ' (size: ' . formatSize(strlen($code)) . ' - ' . $tpl_infos . ') -->';
			$end = '<!-- [' . $level . '] END TEMPLATE #' . $tpl_idx . ' : ' . $file . ' (size: ' . formatSize(strlen($code)) . ' - ' . $tpl_infos . ') -->';

			$code = PHP_EOL . $begin . PHP_EOL . $code . PHP_EOL . $end . PHP_EOL;
		}

		// Layout (2)
		if ($layout_matches) {
			$value = $layout_matches[0];
			$layout = $value[1];

			$layout_code = self::includeFiles($layout, $level-1, $file);
			$code = str_replace($value[0], '', $code);
			
			$layout_code = str_replace('<' . '?=$child_content?' . '>', '{@content}', $layout_code);
			$layout_code = str_replace('{$child_content}', '{@content}', $layout_code);
			$layout_code = str_replace('{@content}', $code, $layout_code);

			$code = $layout_code;
		}

		// includes
		preg_match_all('/{include ?\'?(.*?)\'? ?}/i', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $value) {
			$included_code = self::includeFiles($value[1], $level+1, null, $file);
			$code = str_replace($value[0], $included_code, $code);
		}


		$ts_end = microtime(true);
		$duration = $ts_end - $ts_start;

		if (isset($debugbar_message_idx) && ! is_null($debugbar_message_idx)) {
			$debugbar['templates']->updateMessage($debugbar_message_idx, [
				'tpl' => $file,
				'layout' => $layout,
				'source_length' => strlen($code_init),
				'source_length_str' => formatSize(strlen($code_init)),
				'content_length' => strlen($code),
				'content_length_str' => formatSize(strlen($code)),
				'duration' => round($duration, 6),
				'duration_str' => formatDuration($duration),
				'level' => $level,
			]);
		}

		return $code;
	}

	protected static function compilePHP($code) {
		/* return preg_replace('~\{%\s*(.+?)\s*\%}~is', '<?php $1 ?>', $code); */
		return $code;
	}

	protected static function compileModules($code) {

		// url => {url clients_list}
		$code = preg_replace('/{routeUrl /', '{url ', $code); // for compatibility with old templates
		preg_match_all('~{url (.*?)}~is', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $value) {
			//pre($matches); exit;
			$code = str_replace($value[0], getRouteUrl($value[1]), $code);
		}

		// foreach => {foreach $list as $item}<div>...</div>{/foreach}
		preg_match_all('~{foreach (.*?) ?}(.*?){/foreach}~is', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $value) {
			$replaced = PHP_EOL . '<' . '?php foreach (' . $value[1] . ') : ?' . '>' . PHP_EOL . $value[2] . PHP_EOL . '<' . '?php endforeach; ?' . '>';
			$code = str_replace($value[0], $replaced, $code);
		}

		// if => {if $item}<div>...</div>{/if}
		preg_match_all('~{if (.*?) ?}(.*?){/if}~is', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $value) {
			
			$replaced = '<' . '?php elseif ( $1 ) : ?' . '>';
			$value[2] = preg_replace('/{elseif (.*?) ?}/', $replaced, $value[2]);

			$replaced = PHP_EOL . '<' . '?php if (' . $value[1] . ') : ?' . '>' . PHP_EOL . $value[2] . PHP_EOL . '<' . '?php endif; ?' . '>';
			$code = str_replace($value[0], $replaced, $code);
		}

		return $code;
	}

	protected static function compileEchos($code, $strict=true) {
		// compile PHP variables (method 1) => {$my_var}
		if ($strict) {
			$code = preg_replace('~\{\$(.+?)}~is', '<?php echo \$$1 ?>', $code);
		} else {
			$code = preg_replace('~\{\$(.+?)}~is', '<?php echo isset(\$$1) ? (\$$1) : ""; ?>', $code);
		}
		// compile PHP variables (method 2) => {{ $my_var }}
		return preg_replace('~\{{\s*(.+?)\s*\}}~is', '<?php echo $1 ?>', $code);
	}

	protected static function compileEscapedEchos($code) {
		// compile PHP escaped variables => {{{ $my_var }}}
		return preg_replace('~\{{{\s*(.+?)\s*\}}}~is', '<?php echo htmlentities($1, ENT_QUOTES, \'UTF-8\') ?>', $code);
	}

	protected static function compileBlock($code) {
		preg_match_all('~{block ?(.*?) ?}(.*?){/block}~is', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $value) {
			if (!array_key_exists($value[1], self::$blocks)) self::$blocks[$value[1]] = '';
			if (strpos($value[2], '@parent') === false) {
				self::$blocks[$value[1]] = $value[2];
			} else {
				self::$blocks[$value[1]] = str_replace('@parent', self::$blocks[$value[1]], $value[2]);
			}
			$code = str_replace($value[0], '', $code);
		}
		return $code;
	}

	protected static function compileYield($code) {
		// compile yields => {yield my_block}
		foreach(self::$blocks as $block => $value) {
			$code = preg_replace('/{yield ' . $block . ' ?}/', $value, $code);
		}
		$code = preg_replace('/{yield ?(.*?) ?}/i', '', $code);
		return $code;
	}

}