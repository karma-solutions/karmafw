<?php

namespace KarmaFW\App\Middlewares\DebugBar;

use \DebugBar\DataCollector\ConfigCollector;

use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


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


	public function seoParseContent(Request $request, Response $response)
	{
		$content = $response->getBody();

		$url = $request->getFullUrl();

		preg_match('~<title(.*?)>(.*?)</title>~is', $content, $matches);
		$title = empty($matches) ? '' : $matches[2];

		preg_match('~<meta +name="description" +content="(.*?)" *>~is', $content, $matches);
		$meta_desc = empty($matches) ? '' : $matches[1];

		preg_match('~<h1(.*?)>(.*?)</h1>~is', $content, $matches);
		$h1 = empty($matches) ? '' : $matches[2];

		preg_match_all('/<img /is', $content, $matches);
		$nb_images = empty($matches) ? 0 : count($matches[0]);

		preg_match_all('/<a /is', $content, $matches);
		$nb_links = empty($matches) ? 0 : count($matches[0]);

		preg_match_all('/<script ?/is', $content, $matches);
		$nb_scripts = empty($matches) ? 0 : count($matches[0]);

		preg_match_all('/<script (.*?)src="(.*?)>/is', $content, $matches);
		$nb_scripts_external = empty($matches) ? 0 : count($matches[0]);

		preg_match_all('/<link (.*?)rel="stylesheet"(.*?)>/is', $content, $matches);
		$nb_stylesheets = empty($matches) ? 0 : count($matches[0]);

		$data = [
			'url' => $url,
			'server ip' => $request->getServerIp(),
			'title' => $title,
			'meta description' => $meta_desc,
			'h1' => $h1,
			'nb images' => $nb_images,
			'nb links' => $nb_links,
			'nb stylesheets' => $nb_stylesheets,
			'nb scripts' => $nb_scripts . " (" . ($nb_scripts-$nb_scripts_external) . " inline scripts + " . $nb_scripts_external . " external scripts)",
			'content length' => formatSize(strlen($content)),
		];

		return $data;
	}

}

