<?php

namespace KarmaFW\Database\Sql;

use \DebugBar\DataCollector\DataCollector;
use \DebugBar\DataCollector\Renderable;
use \DebugBar\DataCollector\AssetProvider;


/* USE THIS WITH DEBUGBAR (see http://phpdebugbar.com/ or https://github.com/maximebf/php-debugbar ) */

class SqlDbCollector extends DataCollector implements Renderable, AssetProvider
{
	protected $sql_queries = [];
	protected $totalExecTime = 0;


    public function collect()
    {
	    return [
	        'nb_statements' => count($this->sql_queries),
	        'accumulated_duration' => $this->totalExecTime,
	        'accumulated_duration_str' => round($this->totalExecTime, 5),
	        'statements' => $this->sql_queries,
	    ];
    }

    public function getName()
    {
        return 'sql_queries';
    }


    public function addQuery($query)
    {
    	$this->sql_queries[] = $query;
    	$this->totalExecTime += $query['duration'];
    }


    public function getWidgets()
    {
        return [
            "sql" => [
                "icon" => "inbox",
                "tooltip" => "SQL Queries",
                //"widget" => "PhpDebugBar.Widget",
                "widget" => "PhpDebugBar.Widgets.SQLQueriesWidget",
                //"widget" => "PhpDebugBar.Widgets.MessagesWidget",
                "map" => "sql_queries",
                "default" => "[]"
            ],
            "sql:badge" => [
                "map" => "sql_queries.nb_statements",
                "default" => 0
            ],
        ];
    }


    public function getAssets()
    {
        return array(
            'css' => 'widgets/sqlqueries/widget.css',
            'js' => 'widgets/sqlqueries/widget.js'
        );
    }

}

