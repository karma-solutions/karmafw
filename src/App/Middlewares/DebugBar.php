<?php

namespace KarmaFW\App\Middlewares;

use \DebugBar\StandardDebugBar;
//use \DebugBar\DataCollector\MessagesCollector;
use \DebugBar\DataCollector\ConfigCollector;

use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;
use \KarmaFW\App\Middlewares\DebugBar\KarmaFwCollector;
use \KarmaFW\App\Middlewares\DebugBar\SEOCollector;
use \KarmaFW\App\Middlewares\DebugBar\SqlDbCollector;
use \KarmaFW\App\Middlewares\DebugBar\KarmaMessagesCollector;
//use \KarmaFW\App\Middlewares\DebugBar\PhpTemplateCollector;


class DebugBar
{
	
	public function __invoke(Request $request, Response $response, callable $next)
	{
		$load_debugbar = ( class_exists('\\DebugBar\\StandardDebugBar') && ((defined('ENV') && ENV == 'dev') || defined('FORCE_DEBUGBAR') && FORCE_DEBUGBAR)  );
		$load_debugbar = $load_debugbar && $request->isGet() && ! $request->isAjax() && (! isset($_GET['debugbar']) || ! empty($_GET['debugbar']));

		if ($load_debugbar) {
			$debugbar = new StandardDebugBar();
			App::setData('debugbar', $debugbar);
			
			$debugbar->addCollector(new KarmaFwCollector);
			$debugbar->addCollector(new ConfigCollector);
			$debugbar->addCollector(new SqlDbCollector);
			$debugbar->addCollector(new KarmaMessagesCollector('templates'));
			$debugbar->addCollector(new SEOCollector);

			$debugbarRenderer = $debugbar->getJavascriptRenderer('/assets/vendor/debugbar'); // symlink to ${APP_DIR}/vendor/maximebf/debugbar/src/DebugBar/Resources
		}


		$response = $next($request, $response);

		$is_html = (empty($response->getContentType()) || strpos($response->getContentType(), 'text/html') === 0);
		$show_debugbar = ($load_debugbar && $is_html && $response->getStatus() == 200);

		if ($show_debugbar) {

			// config
			$constants = get_defined_constants(true);
			$debugbar['config']->setData($constants['user']);

			
			// KarmaFW
			$data = [
				'app' => App::getData('app'),
				'request' => $request,
				'response' => $response,
			];
			$debugbar['KarmaFW']->setData($data);


			// SEO
			$seo_data = $this->seoParseContent($response);
			$debugbar['SEO']->setData($seo_data);


			$response->append( $debugbarRenderer->renderHead() );
			// TODO: $response->injectAppendTo('head', $debugbarRenderer->renderHead())
			// => function injectAppendTo($tag) { $body = preg_replace('|</'.$tag.'>|', $injected_html . '</'.$tag.'>', $body, 1);

			$response->append( $debugbarRenderer->render() );
			// TODO: injectAppendTo('body', $debugbarRenderer->render())

			$response->addHeader('X-DebugBar', 'on');

		} else {
			$response->addHeader('X-DebugBar', 'off');
		}


		return $response;
	}


	protected function seoParseContent(Response $response)
	{
		$content = $response->getBody();

		preg_match('~<title(.*?)>(.*?)</title>~is', $content, $matches);
		$title = empty($matches) ? '' : $matches[2];

		preg_match('~<meta +name="description" +content="(.*?)" *>~is', $content, $matches);
		$meta_desc = empty($matches) ? '' : $matches[1];

		preg_match('~<h1(.*?)>(.*?)</h1>~is', $content, $matches);
		$h1 = empty($matches) ? '' : $matches[2];

		preg_match_all('/<a /is', $content, $matches);
		$nb_links = empty($matches) ? 0 : count($matches[0]);

		$data = [
			'title' => $title,
			'meta description' => $meta_desc,
			'h1' => $h1,
			'nb links' => $nb_links,
			'content length' => formatSize(strlen($content)),
		];

		return $data;
	}

}
