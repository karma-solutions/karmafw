<?php

use \KarmaFW\Lib\Email_lib;


function smtp_hook_email($to, $subject, $message_html, $message_text='', $from=null, $from_name=null, $options=[]) {
	// redirige les emails vers vers SMTP_HOOK_EMAIL
	if (! defined('SMTP_HOOK_EMAIL')) {
		return false;
	}
	$to = SMTP_HOOK_EMAIL;
	$options['no_hook'] = true;
	return \KarmaFW\Lib\Email_lib::sendmailSMTP($to, $subject, $message_html, $message_text, $from, $from_name, $options);
}

function smtp_hook_domain($to, $subject, $message_html, $message_text='', $from=null, $from_name=null, $options=[]) {
	// redirige les emails vers vers SMTP_HOOK_DOMAIN  -  exemple:  paul.martin@gmail.com => paul.martin__gmail.com@mon-domaine-a-moi.com
	if (! defined('SMTP_HOOK_DOMAIN')) {
		return false;
	}
	$to = str_replace("@", "__", $to) . "@" . SMTP_HOOK_DOMAIN;
	$options['no_hook'] = true;
	return \KarmaFW\Lib\Email_lib::sendmailSMTP($to, $subject, $message_html, $message_text, $from, $from_name, $options);
}


function smtp_hook_false($to, $subject, $message_html, $message_text='', $from=null, $from_name=null, $options=[]) {
	// n'envoie aucun email et retourne FALSE
	return false;
}

function smtp_hook_true($to, $subject, $message_html, $message_text='', $from=null, $from_name=null, $options=[]) {
	// n'envoie aucun email et retourne TRUE
	return false;
}


