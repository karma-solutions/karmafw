<?php

namespace KarmaFW\Lib\Url;


class Bitly_lib
{

	public static function getBitlyShortUrl($long_url) {

		if (! defined('BITLY_USERNAME') || empty(BITLY_USERNAME)) {
			return false;
		}
		if (! defined('BITLY_APIKEY') || empty(BITLY_APIKEY)) {
			return false;
		}
		
		$result = file_get_contents("http://api.bit.ly/v3/shorten?login=" . BITLY_USERNAME . "&apiKey=" . BITLY_APIKEY . "&longUrl=".urlencode($long_url)."&format=json");
		$short_url = json_decode($result)->data->url;
		return $short_url;
	}

}
