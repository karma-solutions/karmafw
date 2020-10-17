<?php

use \KarmaFW\App\Response;
use \KarmaFW\Routing\Router;


if (! function_exists('pre')) {
	function pre($var, $exit = false, $prefix = '') {
		$out = '';

		$out .= "<pre>";
		if (!empty($prefix)) {
			$out .= $prefix;
		}
		if (is_null($var)) {
			$out .= "NULL";
		} else if ($var === true) {
			$out .= "TRUE";
		} else if ($var === false) {
			$out .= "FALSE";
		} else if (is_string($var)) {
			$out .= '"' . $var . '"';
		} else {
			$out .= print_r($var, true);
		}
		$out .= "</pre>";


		if ($exit) {
			//exit; 

			throw new \Exception($out, 503);

		} else {
			echo $out;
		}
	}
}


if (! function_exists('ifEmpty')) {
	function ifEmpty($val, $default_value='-1') {
		return empty($val) ? $default_value : $val;
	}
}



if (! function_exists('errorHttp')) {
	function errorHttp($error_code, $message='An error has occured', $title='Error') {
		/*
		header("HTTP/1.0 " . $error_code . " " . $title);
		echo '<h1>' . $title . '</h1>';
		echo '<p>' . $message . '</p>';
		exit;
		*/
		$content = '<html>';
		$content .= '<head>';
		$content .= '<title>' . $title . '</title>';
		$content .= '</head>';
		$content .= '<body>';
		$content .= '<h1>' . $title . '</h1>';
		$content .= '<p>' . $message . '</p>';
		$content .= '</body>';
		$content .= '</html>';

		throw new \Exception($content, $error_code);
	}
}


if (! function_exists('redirect')) {
	function redirect($url, $http_code=302) {
		if ($http_code == 'link' || $http_code == 'debug') {
			echo 'continue to <a href="' . $url . '">' . $url . '</a>';
			exit;
		}
		throw new \Exception($url, $http_code);
		//exit;
		//header('Location: ' . $url, true, $http_code);
	}
}


if (! function_exists('get')) {
	function get($key, $default_value=null) {
		return isset($_GET[$key]) ? $_GET[$key] : $default_value;
	}
}

if (! function_exists('post')) {
	function post($key, $default_value=null) {
		return isset($_POST[$key]) ? $_POST[$key] : $default_value;
	}
}

if (! function_exists('session')) {
	function session($key, $default_value=null) {
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default_value;
	}
}

if (! function_exists('cookie')) {
	function cookie($key, $default_value=null) {
		return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default_value;
	}
}


if (! function_exists('slugify')) {
	function slugify($text, $max_length=null) {
		// https://stackoverflow.com/questions/2955251/php-function-to-make-slug-url-string
		// https://stackoverflow.com/questions/3371697/replacing-accented-characters-php

		$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
									'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
									'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
									'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
									'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
		$text = strtr( $text, $unwanted_array );

		// replace non letter or digits by -
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		// trim
		$text = trim($text, '-');

		// remove duplicate -
		$text = preg_replace('~-+~', '-', $text);

		// lowercase
		$text = strtolower($text);

		if (empty($text)) {
			return 'n-a';
		}

		if (! empty($max_length) && strlen($text) > $max_length) {
			$text = substr(0, $max_length);
		}

		return $text;
	}
}


if (! function_exists('generate_uid')) {
	function generate_uid() {
		
		if (function_exists('com_create_guid')) {
			return trim(com_create_guid(), '{}');
		}

		if (function_exists('openssl_random_pseudo_bytes') === true) {
			$data = openssl_random_pseudo_bytes(16);
			$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
			$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
			return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
		}

		mt_srand((double)microtime()*10000);
		
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$uuid = sprintf(
					"%s-%s-%s-%s-%s",
					substr($charid, 0, 8),
					substr($charid, 8, 4),
					substr($charid,12, 4),
					substr($charid,16, 4),
					substr($charid,20,12)
				 );
		return strtolower($uuid);
	}
}



if (! function_exists('generate_password')) {
	function generate_password($nb_chars = 8) {
		$ref = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"; // 62 caractères au total
		$ref = $ref . $ref . $ref; // permet d'avoir jusqu'à 3 fois le meme caractere dans le mot de passe
		$ref = str_shuffle($ref);
		return substr($ref, 0, $nb_chars);
	}
}


if (! function_exists('getRouteUrl')) {
	function getRouteUrl($route_name, $urls_args=[]) {
		return Router::getRouteUrl($route_name, $urls_args);
	}
}


if (! function_exists('date_us_to_fr')) {
	function date_us_to_fr($date_us, $include_time=false) {
		if (empty($date_us)) {
			return null;
		}
		$time = ($include_time) ? substr($date_us, 10) : "";
		$date_us = substr($date_us, 0, 10);
		$parts = explode('-', $date_us);
		return implode('/', array_reverse($parts)) . $time;
	}
}

if (! function_exists('date_us2_to_fr')) {
	function date_us2_to_fr($date_us, $include_time=false) {
		if (empty($date_us)) {
			return null;
		}
		$time = ($include_time) ? substr($date_us, 10) : "";
		$date_us = substr($date_us, 0, 10);
		$parts = explode('/', $date_us);
		$parts2 = [
			substr('00' . $parts[1], -2),
			substr('00' . $parts[0], -2),
			$parts[2],
		];
		return implode('/', $parts2) . $time;
	}
}

if (! function_exists('date_fr_to_us')) {
	function date_fr_to_us($date_fr, $include_time=false) {
		if (empty($date_fr)) {
			return null;
		}
		$time = ($include_time) ? substr($date_fr, 10) : "";
		$date_fr = substr($date_fr, 0, 10);
		$parts = explode('/', $date_fr);
		return implode('-', array_reverse($parts)) . $time;
	}
}


if (! function_exists('truncate_str')) {
	function truncate_str($str, $max_length) {
		if (strlen($str) > $max_length) {
			$str = substr($str, 0, $max_length-1) . '…';
		}
		return $str;
	}
}


if (! function_exists('get_url_path')) {
	function get_url_path($url, $with_querystring=true, $with_url_hash=false) {
		$url_parts = parse_url($url);
		$url = $url_parts['path'];

		if ($with_querystring && ! empty($url_parts['query'])) {
			$url .= '?' . $url_parts['query'];
		}
		if ($with_url_hash && ! empty($url_parts['fragment'])) {
			$url .= '#' . $url_parts['fragment'];
		}

		return $url;
	}
}


if (! function_exists('rrmdir')) {
	function rrmdir($src) {
		// https://www.php.net/manual/fr/function.rmdir.php
	    $dir = opendir($src);
	    while(false !== ( $file = readdir($dir)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            $full = $src . '/' . $file;
	            if ( is_dir($full) ) {
	                rrmdir($full);
	            }
	            else {
	                unlink($full);
	            }
	        }
	    }
	    closedir($dir);
	    rmdir($src);
	}
}
