<?php

namespace KarmaFW\Lib\Auth;

use KarmaFW\Lib\Sms\Sms_lib;


class SmsAuthenticator_lib
{

	public static function sendSms($recipient, $message, $provider=null)
	{
		if ($provider == 'freemobile' || (defined('FREEMOBILE_PHONE_NUMBER' && FREEMOBILE_PHONE_NUMBER == $recipient))) {
			if ($provider == 'freemobile' || (defined('FREEMOBILE_API_KEY') && FREEMOBILE_API_KEY != '' && defined('FREEMOBILE_API_SECRET') && FREEMOBILE_API_SECRET != '')) {
				// Envoi via FreeMobile
				return Sms_lib::sendSmsFreeMobile($message);
			}
		}

		// Envoi via SmsEnvoi
		return Sms_lib::sendSmsSmsEnvoi($recipient, $message, APP_NAME);

		//return Sms_lib::sendSmsTwilio($recipient, $message);
	}

	public static function generateCode()
	{
		$code = rand(100000, 999999);
		return $code;
	}

	public static function checkCode($entered_code) 
	{
		return ! empty($_SESSION['2fa']['code']) && $_SESSION['2fa']['code'] == $entered_code;
	}

}

