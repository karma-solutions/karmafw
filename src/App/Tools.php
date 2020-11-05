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


    public static function getCaller($excludeFiles = [], $formatted = true, $traceOffset = 2)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $backtrace = array_slice($backtrace, $traceOffset);

        $excludeFiles[] = VENDOR_DIR . '/karmasolutions/karmafw/src/Database/Sql/SqlTable.php';
        $excludeFiles[] = VENDOR_DIR . '/karmasolutions/karmafw/src/Database/Sql/SqlTableModel.php';
        //$excludeFiles[] = VENDOR_DIR . '/karmasolutions/karmafw/src/Database/Sql/SqlQuery.php';
        //$excludeFiles[] = VENDOR_DIR . '/karmasolutions/karmafw/src/App/Middlewares/DebugBar.php';
        //$excludeFiles[] = VENDOR_DIR . '/karmasolutions/karmafw/src/Routing/Router.php';
        //$excludeFiles[] = VENDOR_DIR . '/karmasolutions/karmafw/src/App/Pipe.php';

        foreach ($backtrace as $index => $context) {
            if (isset($context['file']) && ! in_array($context['file'], $excludeFiles)) {
                break;
            }
        }

        if (!isset($context)) {
            return null;
        }

        if ($formatted) {
            return isset($context) && array_key_exists('file', $context) ? $context['file'] . ':' . $context['line'] : null;
        }

        return $context;
    }
}
