<?php

namespace KarmaFW\App\Middlewares\DebugBar;

use \DebugBar\DataCollector\ConfigCollector;

use \KarmaFW\App;


class SEOCollector extends ConfigCollector
{
	protected $data = [
		'title' => '',
		'meta description' => '',
		'h1' => '',
	];


	public function __construct(array $data = array(), $name = 'config')
	{
		$data += $this->data;
		parent::__construct($data, $name);
	}


    public function getName()
    {
        return 'SEO';
    }

    public function collect()
    {
        return parent::collect();
    }

}

