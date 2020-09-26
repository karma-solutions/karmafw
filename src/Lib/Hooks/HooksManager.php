<?php

namespace KarmaFW\Lib\Hooks;


// DEFINE YOUR CUSTOM HOOKS
/*
\KarmaFW\Lib\Hooks\HooksManager::addHookAction('webcontroller.init', function ($controller) {
    echo "webcontroller hooked<hr />";
});
*/


class HooksManager {
	// source: https://stackoverflow.com/questions/5931324/what-is-a-hook-in-php

    private static $actions = [];

    public static function applyHook($hook, $args = array()) {
        if (!empty(self::$actions[$hook])) {
            foreach (self::$actions[$hook] as $f) {
                $f($args);
            }
        }
    }

    public static function addHookAction($hook, $function) {
        self::$actions[$hook][] = $function;
    }

}

