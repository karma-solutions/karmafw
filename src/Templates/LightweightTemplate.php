<?php

namespace KarmaFW\Templates;

use \KarmaFW\App;


class LightweightTemplate {
	// https://codeshack.io/lightweight-template-engine-php/

	static $blocks = array();
	static $cache_path = APP_DIR . '/var/cache';
	static $tpl_path = APP_DIR . '/templates';
	static $cache_enabled = (ENV == 'dev') && false;


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

	
	public static function view($file, $data = array()) {
		$cached_file = self::cache($file);
	    extract($data, EXTR_SKIP);
	   	require $cached_file;
	}

	protected static function cache($file) {
		if (!file_exists(self::$cache_path)) {
		  	mkdir(self::$cache_path, 0744);
		}
	    $cached_file = self::$cache_path . '/' . str_replace(array('/', '.html'), array('_', ''), $file . '.php');
	    if (!self::$cache_enabled || !file_exists($cached_file) || filemtime($cached_file) < filemtime(self::$tpl_path . '/' . $file)) {
			$code = self::includeFiles($file);
			$code = self::compileCode($code);
	        file_put_contents($cached_file, '<?php class_exists(\'' . __CLASS__ . '\') or exit; ?>' . PHP_EOL . $code);

	    } else {
	    	//header('X-Template: cached');

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
		$code = self::compileEscapedEchos($code);
		$code = self::compileEchos($code);
		$code = self::compilePHP($code);
		return $code;
	}

	protected static function includeFiles($file, $caller_file=null, $parent_file=null) {
		$code = file_get_contents(self::$tpl_path . '/' . $file);
		$code_init = $code;
		$layout = null;

		$debugbar = App::getData('debugbar');
		if ($debugbar) {
			if (isset($debugbar['templates'])) {
				$debugbar_message_idx = $debugbar['templates']->addMessage([
					'tpl' => $file,
				]);
			}
		}

		$ts_start = microtime(true);


		if (defined('ENV') && ENV == 'dev') {
			$suffix = '';
			if ($caller_file) {
				$suffix .= ' => caller: ' . $caller_file . '';
			}
			if ($parent_file) {
				$suffix .= ' => parent: ' . $parent_file . '';
			}
			$code = '<!-- TEMPLATE START : ' . $file . $suffix . ' -->' . $code . '<!-- TEMPLATE END : ' . $file . ' => size: ' . formatSize(strlen($code)) . ' -->';
		}

		// Layout
		preg_match_all('/{layout ?\'?(.*?)\'? ?}/i', $code, $matches, PREG_SET_ORDER);
		if ($matches) {
			$value = $matches[0];
			$layout = $value[1];

			$layout_code = self::includeFiles($layout, $file);
			$code = str_replace($value[0], '', $code);
			
			$layout_code = str_replace('<' . '?=$child_content?' . '>', '{$child_content}', $layout_code);
			$layout_code = str_replace('{$child_content}', $code, $layout_code);

			$code = $layout_code;
		}

		// includes
		preg_match_all('/{include ?\'?(.*?)\'? ?}/i', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $value) {
			$included_code = self::includeFiles($value[1], null, $file);
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
				'duration' => $duration,
				'duration_str' => formatDuration($duration),
			]);
		}

		return $code;
	}

	protected static function compilePHP($code) {
		return preg_replace('~\{%\s*(.+?)\s*\%}~is', '<?php $1 ?>', $code);
	}

	protected static function compileEchos($code, $strict=false) {
		if ($strict) {
			$code = preg_replace('~\{\$(.+?)}~is', '<?php echo \$$1 ?>', $code);

		} else {
			$code = preg_replace('~\{\$(.+?)}~is', '<?php echo isset(\$$1) ? (\$$1) : ""; ?>', $code);
		}
		return preg_replace('~\{{\s*(.+?)\s*\}}~is', '<?php echo $1 ?>', $code);
	}

	protected static function compileEscapedEchos($code) {
		return preg_replace('~\{{{\s*(.+?)\s*\}}}~is', '<?php echo htmlentities($1, ENT_QUOTES, \'UTF-8\') ?>', $code);
	}

	protected static function compileBlock($code) {
		preg_match_all('/{% ?block ?(.*?) ?%}(.*?){% ?endblock ?%}/is', $code, $matches, PREG_SET_ORDER);
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
		foreach(self::$blocks as $block => $value) {
			$code = preg_replace('/{% ?yield ?' . $block . ' ?%}/', $value, $code);
		}
		$code = preg_replace('/{% ?yield ?(.*?) ?%}/i', '', $code);
		return $code;
	}

}