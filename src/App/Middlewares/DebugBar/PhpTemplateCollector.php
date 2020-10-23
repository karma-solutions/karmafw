<?php

namespace KarmaFW\App\Middlewares\DebugBar;

use \DebugBar\DataCollector\DataCollector;
use \DebugBar\DataCollector\Renderable;
use \DebugBar\DataCollector\AssetProvider;


/* USE THIS WITH DEBUGBAR (see http://phpdebugbar.com/ or https://github.com/maximebf/php-debugbar ) */


// DO NOT WORK


class PhpTemplateCollector extends DataCollector implements Renderable, AssetProvider
{
    protected $templates = [];


    public function collect()
    {

        return [
            'test1',
            'test2',
        ];


	    return [
            'nb_templates' => count($this->templates),
            'templates' => $this->templates,
	    ];
    }

    public function getName()
    {
        return 'templates';
    }


    public function getWidgets()
    {
        return [
            "templates" => [
                "icon" => "inbox",
                "tooltip" => "Templates PHP",
                //"widget" => "PhpDebugBar.Widget",
                "widget" => "PhpDebugBar.Widgets.MessagesWidget",
                "map" => "templates",
                "default" => "[]"
            ],
        ];
    }


    public function getAssets()
    {
        return array(
            //'css' => 'widgets/sqlqueries/widget.css',
            //'js' => 'widgets/sqlqueries/widget.js'
        );
    }

}

