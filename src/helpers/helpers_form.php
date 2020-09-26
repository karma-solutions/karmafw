<?php

function checkEmail($email) {
	// format: abc123@cde456.aa | abc123@cde456.aaa
	return !! preg_match(" /^[^\W][a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}$/ ", $email);
}

function checkPhone($phone) {
	// format: 00 00 00 00 00,
	return !! preg_match(" \^(\d\d\s){4}(\d\d)$\ ", $phone);
}

function checkUrl($url) {
	// format http://www.example.com | www.example.com | http://subdomain.example.com | example.com
	return !! preg_match(" \^(http|https|ftp):\/\/([\w]*)\.([\w]*)\.(com|net|org|biz|info|mobi|us|cc|bz|tv|ws|name|co|me)(\.[a-z]{1,3})?\z/i ", $url);
}

function checkLogin($login, $min_length=3, $max_length=16) {
	// format: abc_123
	return !! preg_match(" \^[a-zA-Z0-9_]{" . $min_length . "," . $max_length . "}$\ ", $login);
}

function checkDateFr($date) {
	// format: 00/00/0000
	return !! preg_match(" \^([0-3][0-9]})(/)([0-9]{2,2})(/)([0-3]{2,2})$\ ", $date);
}

function checkZipcode($zipcode) {
	// format: 00000
	return !! preg_match(" \^[0-9]{5,5}$\ ", $zipcode);
}

function checkIPv4($ip) {
	return !! preg_match(" \^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$\ ", $ip);
}

function checkHexColor($color) {
	return !! preg_match(" \^#(?:(?:[a-f\d]{3}){1,2})$/i ", $color);
}

