<?php

namespace KarmaFW\App\Middlewares;

use \DebugBar\StandardDebugBar;
//use \DebugBar\DataCollector\MessagesCollector;
use \DebugBar\DataCollector\TimeDataCollector;

use \KarmaFW\App;
use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;
use \KarmaFW\App\Middlewares\DebugBar\SqlDbCollector;
use \KarmaFW\App\Middlewares\DebugBar\KarmaMessagesCollector;
//use \KarmaFW\App\Middlewares\DebugBar\PhpTemplateCollector;


class DebugBar
{
	
	public function __invoke(Request $request, Response $response, callable $next)
	{
		$load_debugbar = (defined('ENV') && ENV == 'dev');

		if ($load_debugbar) {
			$debugbar = new StandardDebugBar();
			App::setData('debugbar', $debugbar);
			
			$debugbar->addCollector(new SqlDbCollector);

			//$debugbar->addCollector(new PhpTemplateCollector); // DO NOT WORK
			$debugbar->addCollector(new KarmaMessagesCollector('templates'));

			$debugbarRenderer = $debugbar->getJavascriptRenderer('/assets/vendor/debugbar'); // symlink to ${APP_DIR}/vendor/maximebf/debugbar/src/DebugBar/Resources
		}


		$response = $next($request, $response);

		$is_html = (empty($response->getContentType()) || strpos($response->getContentType(), 'text/html') === 0);
		$show_debugbar = ($load_debugbar && $is_html);

		if ($show_debugbar) {
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

}
