<?php

namespace KarmaFW\Lib\Email;

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\SMTP;
use \PHPMailer\PHPMailer\Exception;


class Email_lib
{

	public static function sendmail($to, $subject, $message, $from, $from_name=null)
	{
		$headers = "";
		return mail($to, $subject, $message, $headers);
	}


	public static function sendmailSMTP($to, $subject, $message_html, $message_text='', $from=null, $from_name=null, $options=[]) {

		if (defined('SMTP_HOOK') && empty($options['no_hook'])) {
			$smtp_hook_func = SMTP_HOOK;
			if (is_callable($smtp_hook_func)) {
				return $smtp_hook_func($to, $subject, $message_html, $message_text, $from, $from_name);
			}
			// aucun message envoyé ni hook
			return boolval($smtp_hook_func); // permet de mettre "1" pour un retour ok ou "0" pour un retour en erreur
		}

		if (false) {
			$mail = [
				'from' => $from,
				'from_name' => $from_name,
				'to' => $to,
				'subject' => $subject,
				'message_html' => $message_html,
				'message_text' => $message_text,
			];
			//echo "Emails désactivés";
			//pre($mail, 1);
			return false;
		}

		$mail = new PHPmailer();

		$mail->IsSMTP();
		$mail->IsHTML(! empty($message_html));
		$mail->CharSet = "UTF-8";

		if (false) {
			$mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
		}

		if (defined('SMTP_HOST') && ! empty(SMTP_HOST)) {
			//$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$mail->SMTPAuth = SMTP_SMTPAUTH;
			$mail->Host = SMTP_HOST;
			$mail->Port = SMTP_PORT;
			$mail->Username = SMTP_USERNAME;
			$mail->Password = SMTP_PASSWORD;

		} else {
			$mail->Host = 'localhost';
			$mail->Port = 25;
		}

		if (empty($from)) {
			$processUser = posix_getpwuid(posix_geteuid());
			//$from = $processUser['name'] . "@" . gethostname();
			$from = $processUser['name'] . "@localhost";
		}
		$mail->From = $from;

		if (! empty($from_name)) {
			$mail->FromName = $from_name;
		}

		$mail->AddAddress($to); 

		$mail->Subject = $subject;

		if (! empty($message_html)) {
			$mail->Body = $message_html;

		    if (! empty($message_text)) {
		    	$mail->AltBody = $message_text;
		    }

		} else {
			$mail->Body = $message_text;
		}

		//$mail->SMTPDebug = 4;

		if(!$mail->Send()){
			if (false) {
				print_r($mail);
				echo $mail->ErrorInfo . PHP_EOL;
			}
			$ok = false;
		} else {
			//echo 'Mail envoyé avec succès';
			$ok = true;
		}
		$mail->SmtpClose();
		unset($mail);

		return $ok;
	}

}
