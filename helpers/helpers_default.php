<?php


if (! function_exists('pre')) {
	function pre($var, $exit = false, $prefix = '') {
		echo "<pre>";
		if (!empty($prefix)) {
			echo $prefix;
		}
		print_r($var);
		echo "</pre>";

		if ($exit) {
			exit;
		}
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
