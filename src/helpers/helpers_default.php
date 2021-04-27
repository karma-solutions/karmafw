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

			throw new \Exception($out, 200);

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
			$text = substr($text, 0, $max_length);
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
		//pre($date_us, "date_us2_to_fr: "); exit;

		if (empty($date_us) || strlen($date_us) < 8) {
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



if (! function_exists('formatSize')) {

	function formatSize($bytes, $format = '%.2f',$lang = 'fr') {
		// http://dev.petitchevalroux.net/php/afficher-taille-fichier-avec-une-unite-php.271.html

		if (empty($bytes)) {
			return '0';
		}

		static $units = array(
			'fr' => array(
				'o',
				'Ko',
				'Mo',
				'Go',
				'To'
			),
			'en' => array(
				'B',
				'KB',
				'MB',
				'GB',
				'TB'
			),
		);
		$translatedUnits = &$units[$lang];

		if(isset($translatedUnits)  === false) {
			$translatedUnits = &$units['en'];
		}

		$b = (double)$bytes;

		/*On gére le cas des tailles de fichier négatives*/
		if ($b > 0) {
			$e = (int)(log($b,1024));
			/**Si on a pas l'unité on retourne en To*/
			if(isset($translatedUnits[$e]) === false) {
				$e = 4;
			}
			$b = $b/pow(1024,$e);
		} else {
			$b = 0;
			$e = 0;
		}

		return sprintf($format.' %s',$b,$translatedUnits[$e]);
	}
}


if (! function_exists('formatTel')) {
	function formatTel($num, $sep=' ') {
	    if (is_null($num)) {
	        return null;
	    }
		$num = trim($num);
		$num = str_replace(' ', '', $num);
		$num = str_replace('.', '', $num);
		$num = str_replace('-', '', $num);

		if (strlen($num) == 10) {
			$parts = str_split($num, 2);
			return implode($sep, $parts);
		}

		if (strlen($num) == 12 && substr($num, 0, 3) == '+33') {
			$parts = str_split(substr($num, 4), 2);
			return substr($num, 0, 3) . ' ' . substr($num, 3, 1) . ' ' . implode($sep, $parts);
		}

		return $num;

	}
}


if (! function_exists('formatPrice')) {
	function formatPrice($price, $devise='') {
	    if (empty($price)) {
	        $price = 0;
	    }
		$str = number_format($price, 2, ".", " ");
		if (! empty($devise)) {
			$str .= " " . $devise;
		}
		return $str;
	}
}


if (! function_exists('formatDuration')) {
	function formatDuration($seconds) {
	    if (empty($seconds)) {
	        return 0 . " s";

	    } else if ($seconds < 1/1000) {
	        return round($seconds*1000*1000, 4) . " µs";

	    } else if ($seconds < 1) {
	        return round($seconds*1000, 4) . " ms";

	    } else if ($seconds < 60) {
	        return $seconds . " s";

	    } else if ($seconds < 3600) {
	        $minutes = floor($seconds/60);
	        $seconds2 = $seconds - ($minutes * 60);
	        return $minutes . " min " . $seconds2 . " s";

	    } else if ($seconds < 86400) {
	        $hours = floor($seconds/3600);
	        $seconds2 = $seconds - ($hours * 3600);
	        
	        $minutes = floor($seconds2/60);
	        $seconds3 = $seconds2 - ($minutes * 60);
	        return $hours . " h " . $minutes . " min"; // . " " . $seconds3 . " s";

	    } else {
	        $days = floor($seconds/86400);
	        $seconds2 = $seconds - ($days * 3600);

	        $hours = floor($seconds2/3600);
	        $seconds3 = $seconds2 - ($hours * 3600);
	        
	        $minutes = floor($seconds3/60);
	        $seconds4 = $seconds3 - ($minutes * 60);
	        return $days . " d " . $hours . " h";// . " " . $minutes . " min " . $seconds4 . " s"
	    }
	}
}


if (! function_exists('IPv6ToIPv4')) {
	function IPv6ToIPv4($ip) {
		// source: https://stackoverflow.com/questions/12435582/php-serverremote-addr-shows-ipv6

		/*
		Fonctionne uniquement pour les IPv4 encapsulées dans IPv6 :

		::ffff:192.000.002.123
		::ffff:192.0.2.123
		0000:0000:0000:0000:0000:ffff:c000:027b
		::ffff:c000:027b
		::ffff:c000:27b
		192.000.002.123
		192.0.2.123
		*/


		// Known prefix
		$v4mapped_prefix_hex = '00000000000000000000ffff';
		//$v4mapped_prefix_bin = pack("H*", $v4mapped_prefix_hex); // PHP < 5.4
		$v4mapped_prefix_bin = hex2bin($v4mapped_prefix_hex);  // PHP >= 5.4

		// Parse
		$addr_bin = inet_pton($ip);
		if( $addr_bin === FALSE ) {
			// Unparsable? How did they connect?!?
			return null;
		}

		// Check prefix
		if( substr($addr_bin, 0, strlen($v4mapped_prefix_bin)) == $v4mapped_prefix_bin) {
			// Strip prefix
			$addr_bin = substr($addr_bin, strlen($v4mapped_prefix_bin));
		}

		// Convert back to printable address in canonical form
		$ip4 = inet_ntop($addr_bin);

		return $ip4;
	}
}