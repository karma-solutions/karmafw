<?php

namespace KarmaFW\App\Middlewares\DebugBar;

use \DebugBar\DataCollector\DataCollector;
use \DebugBar\DataCollector\Renderable;
use \DebugBar\DataCollector\AssetProvider;


/* USE THIS WITH DEBUGBAR (see http://phpdebugbar.com/ or https://github.com/maximebf/php-debugbar ) */

class SqlDbCollector extends DataCollector implements Renderable, AssetProvider
{
	protected $sql_queries = [];
	protected $totalExecTime = 0;
    protected $total_memory = 0;
    protected $max_memory = 0;
    protected $nb_failed_statements = 0;


    public function collect()
    {
	    return [
	        'nb_statements' => count($this->sql_queries),
	        'accumulated_duration' => $this->totalExecTime,
	        'accumulated_duration_str' => formatDuration($this->totalExecTime),
	        'statements' => $this->sql_queries,
            'nb_failed_statements' => $this->nb_failed_statements,
            'memory_usage' => $this->total_memory,
            'memory_usage_str' => formatSize($this->total_memory),
            'peak_memory_usage' => $this->max_memory,
            'peak_memory_usage_str' => formatSize($this->max_memory),
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

        $this->total_memory += $query['memory'];
        $this->max_memory = max($this->max_memory, $query['memory']);

        if ($query['is_success'] === false) {
            $this->nb_failed_statements += 1;
        }
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

