<?php

namespace KarmaFW\App\Middlewares\DebugBar;

use \DebugBar\DataCollector\MessagesCollector;

use \KarmaFW\App;


class KarmaMessagesCollector extends MessagesCollector
{

	public function addMessage($message, $label = 'info', $isString = true)
	{
		parent::addMessage($message, $label, $isString);
		return count($this->messages) - 1;
	}

	public function updateMessage($message_idx, $message='')
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

        $this->messages[$message_idx]['message'] = $messageText;
        $this->messages[$message_idx]['message_html'] = $messageHtml;
        $this->messages[$message_idx]['is_string'] = $isString;
	}

}

