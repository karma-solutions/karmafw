<?php

namespace KarmaFW\Lib\Auth;

use \PHPGangsta_GoogleAuthenticator;


class GoogleAuthenticator_lib
{

	public static function generateSecret($length=16)
	{
		$ga = new PHPGangsta_GoogleAuthenticator();
		$secret = $ga->createSecret($length);
		//echo "Secret is: ".$secret."\n\n";
		return $secret;
	}

	public static function getQrCode($secret)
	{
		$ga = new PHPGangsta_GoogleAuthenticator();
		
		$qrCodeUrl = $ga->getQRCodeGoogleUrl(APP_NAME, $secret);
		//echo "Google Charts URL for the QR-Code: " . $qrCodeUrl . "\n\n";

		return $qrCodeUrl;
	}

	public static function checkCode($secret, $entered_code) 
	{
		$ga = new PHPGangsta_GoogleAuthenticator();
		
		/*
		$oneCode = $ga->getCode($secret);
		echo "Checking Code '$oneCode' and Secret '$secret':\n";

		if ($entered_code != $oneCode) {
			echo "Wrong code ($entered_code != $oneCode)";
		}
		*/

		$checkResult = $ga->verifyCode($secret, $entered_code, 2);    // 2 = 2*30sec clock tolerance
		if ($checkResult) {
			//echo 'OK';
		} else {
			//echo 'FAILED';
		}
		return $checkResult;
	}

}

