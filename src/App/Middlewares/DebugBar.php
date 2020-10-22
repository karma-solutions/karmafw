<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;

use \DebugBar\StandardDebugBar;

class DebugBar
{
	
	public function __invoke(Request $request, Response $response, callable $next)
	{
		$debugbar = new StandardDebugBar();
		//$debugbar->addCollector(new MessagesCollector('sql'));

		$debugbarRenderer = $debugbar->getJavascriptRenderer('/assets/vendor/debugbar'); // symlink to ${APP_DIR}/vendor/maximebf/debugbar/src/DebugBar/Resources

		$response = $next($request, $response);


		$response->append( $debugbarRenderer->renderHead() );
		$response->append( $debugbarRenderer->render() );


		return $response;
	}

}
