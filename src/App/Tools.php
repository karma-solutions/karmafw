<?php

namespace KarmaFW\App;


class Tools
{

	public static function loadHelpers($dir)
	{
		$helpers = glob($dir . '/helpers_*.php');

		foreach ($helpers as $helper) {
			require $helper;
		}
	}


	public static function isCli()
	{
		return (php_sapi_name() == 'cli');
	}

}
