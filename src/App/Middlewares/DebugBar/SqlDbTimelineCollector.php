<?php

namespace KarmaFW\App\Middlewares\DebugBar;

use \DebugBar\DataCollector\TimeDataCollector;
use \DebugBar\DataCollector\Renderable;


/* USE THIS WITH DEBUGBAR (see http://phpdebugbar.com/ or https://github.com/maximebf/php-debugbar ) */

class SqlDbTimelineCollector extends TimeDataCollector implements Renderable
{
	
    public function getName()
    {
        return 'sql_time';
    }

    public function getWidgets()
    {
        return array(
            "sql_time" => array(
                "icon" => "clock-o",
                "tooltip" => "Request Duration",
                "map" => "sql_time.duration_str",
                "default" => "'0ms'"
            ),
            "SQL timeline" => array(
                "icon" => "tasks",
                "widget" => "PhpDebugBar.Widgets.TimelineWidget",
                "map" => "sql_time",
                "default" => "{}"
            )
        );
    }
}

