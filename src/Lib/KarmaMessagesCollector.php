<?php

namespace KarmaFW\Lib;

use \DebugBar\DataCollector\MessagesCollector;

use \KarmaFW\App;


class KarmaMessagesCollector extends MessagesCollector
{

	public function addMessage($message, $label = 'info', $isString = true)
	{
		parent::addMessage($message, $label, $isString);
		return $this->messages[ count($this->messages) - 1 ];
	}

	public function updateMessage($messageObj, $message)
	{
        $messageText = $message;
        $messageHtml = null;
        if (!is_string($message)) {
            // Send both text and HTML representations; the text version is used for searches
            $messageText = $this->getDataFormatter()->formatVar($message);
            if ($this->isHtmlVarDumperUsed()) {
                $messageHtml = $this->getVarDumper()->renderVar($message);
            }
            $isString = false;
        }
        
        $messageObj['message'] = $messageText;
        $messageObj['message_html'] = $messageHtml;
        $messageObj['is_string'] = $isString;
	}

}

