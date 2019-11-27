<?php

use \KarmaFW\Routing\Router;


if (! function_exists('pre')) {
	function pre($var, $exit = false, $prefix = '') {
		echo "<pre>";
		if (!empty($prefix)) {
			echo $prefix;
		}
		if (is_null($var)) {
			echo "NULL";
		} else if ($var === true) {
			echo "TRUE";
		} else if ($var === false) {
			echo "FALSE";
		} else if (is_string($var)) {
			echo '"' . $var . '"';
		} else {
			print_r($var);
		}
		echo "</pre>";

		if ($exit) {
			exit;
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
		header("HTTP/1.0 " . $error_code . " " . $title);
		echo '<h1>' . $title . '</h1>';
		echo '<p>' . $message . '</p>';
		exit;
	}
}


if (! function_exists('redirect')) {
	function redirect($url, $http_code=302) {
		header('Location: ' . $url, true, $http_code);
		exit;
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
	function date_us_to_fr($date_us) {
	    $date_us = substr($date_us, 0, 10);
	    $parts = explode('-', $date_us);
	    return implode('/', array_reverse($parts));
	}
}


if (! function_exists('date_us_to_fr')) {
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

