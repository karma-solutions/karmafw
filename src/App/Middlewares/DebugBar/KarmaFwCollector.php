<?php

namespace KarmaFW\App\Middlewares\DebugBar;

use \DebugBar\DataCollector\ConfigCollector;

use \KarmaFW\App;


class KarmaFwCollector extends ConfigCollector
{

    public function getName()
    {
        return 'KarmaFW';
    }

    public function collect()
    {
        return parent::collect();
    }

}

