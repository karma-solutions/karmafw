<?php

namespace KarmaFW\Lib\Sms;


class Sms_lib
{


	public static function sendSmsFreeMobile($message)
	{
		if (! defined('FREEMOBILE_API_KEY') || empty(FREEMOBILE_API_KEY)) {
			return false;
		}
		if (! defined('FREEMOBILE_API_SECRET') || empty(FREEMOBILE_API_SECRET)) {
			return false;
		}
		$API_KEY = FREEMOBILE_API_KEY;
		$API_SECRET = FREEMOBILE_API_SECRET;
		$url = "https://smsapi.free-mobile.fr/sendmsg?user=" . $API_KEY . "&pass=" . $API_SECRET . "&msg=" . urlencode($message);
		$result = @file_get_contents($url);
		if ($result === false) {
			return false;
		}
		if ($result === '') {
			return true;
		}
		return false;
	}


	public static function sendSmsSmsEnvoi($numero, $message, $sender=null)
	{
		// https://www.smsenvoi.com/api-sms/
		// https://www.smsenvoi.com/api-sms/librairie-php/tutoriel-comment-envoyer-des-sms-en-php/ (7 à 10 centime par sms selon forfait)
		// https://www.smsenvoi.com/site/webroot/API/API_SMSENVOI_HTTP_V2.pdf


		if (! defined('SMSENVOI_API_KEY') || empty(SMSENVOI_API_KEY)) {
			return false;
		}
		if (! defined('SMSENVOI_SECRET_KEY') || empty(SMSENVOI_SECRET_KEY)) {
			return false;
		}
		
		$url = "https://www.smsenvoi.com/httpapi/sendsms/";

		$params = array(
			"email" => SMSENVOI_API_KEY,
			'apikey' => SMSENVOI_SECRET_KEY,
			"message[type]" => 'sms',
			"message[subtype]" => 'STANDARD',
			//"message[senderlabel]" => 'PHP App',
			"message[content]" => $message,
			"message[recipients]" => $numero,
		);
		if (! empty($sender)) {
			$params['message[senderlabel]'] = $sender;
		}
		$postdata = http_build_query($params);

		$opts = array(
			'http' => array(
		        'method'  => 'POST',
		        'header'  => "Content-type: application/x-www-form-urlencoded",
		        'content' => $postdata
	    	)
	        , 'ssl' => array(
		        "verify_peer" => false,
		        "verify_peer_name" => false,
	        )
		);
		$context = stream_context_create($opts);

		//echo $url; exit;
		$result_json = file_get_contents($url, false, $context);
		$result = json_decode($result_json);

		return ! empty($result->success);
	}



	/*
	public static function sendSmsTwilio($numero, $message)
	{
		// https://www.twilio.com/docs/api/messaging/send-messages

		if (! defined('TWILIO_API_KEY') || empty(TWILIO_API_KEY)) {
			return false;
		}
		if (! defined('TWILIO_API_SECRET') || empty(TWILIO_API_SECRET)) {
			return false;
		}
		
		$url = "https://api.twilio.com/2010-04-01/Accounts/" . TWILIO_API_KEY . "/Messages.json";

		$params = array(
			"To" => urlencode($numero),
			//"From" => urlencode("+33682437831"),
			"From" => urlencode("+33644646493"),
			"Body" => urlencode($message),
			//"MediaUrl" => "http://www.example.com/cheeseburger.png",
		);
		$postdata = http_build_query($params);

		$opts = array(
			'http' => array(
		        'method'  => 'POST',
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n" . "Authorization: Basic " . base64_encode(TWILIO_API_KEY . ":" . TWILIO_API_SECRET),
		        'content' => $postdata
	    	)
		);
		//print_r($opts);
		$context = stream_context_create($opts);

		// NE FONCTIONNE PAS => HTTP/1.1 400 BAD REQUEST  => A DEBUGER

		//echo $url; exit;
		$result = file_get_contents($url, false, $context);
		echo $result; exit;
	}
	*/
	
}


//Sms_lib::sendSmsFreeMobile('test ' . date('Y-m-d H:i:s'));


if (realpath($argv[0]) == realpath(__FILE__) && ! empty($argv[1]) ) {
	Sms_lib::sendSmsFreeMobile($argv[1]);
	echo "SMS sent !" . PHP_EOL;
}

// php Sms_lib.php "mon message envoyé par l'api Free"
