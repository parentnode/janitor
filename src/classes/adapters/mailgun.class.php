<?php
/**
* @package janitor.shop
*/

require_once('includes/mailgun-php-2.3/vendor/autoload.php');

use Mailgun\Mailgun;


class JanitorMailgun {


	// Mailer settings
	private $api_key;
	private $domain;



	function __construct($_settings) {

		# Instantiate the client.
		$this->domain = $_settings["domain"];
		$this->api_key = $_settings["api-key"];


		$this->client = Mailgun::create($this->api_key);

	}

	function send($_options) {

		$subject = false;
		$text = false;
		$html = false;

		$from_name = false;
		$from_email = false;
		$recipients = false;

		$attachments = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "subject"                : $subject                = $_value; break;
					case "text"                   : $text                   = $_value; break;
					case "html"                   : $html                   = $_value; break;

					case "from_name"              : $from_name              = $_value; break;
					case "from_email"             : $from_email             = $_value; break;
					case "recipients"             : $recipients             = $_value; break;

					case "attachments"            : $attachments            = $_value; break;

				}
			}
		}


		$mail_options = [];

		$mail_options["subject"] = $subject;
		$mail_options["from"] = "$from_name <$from_email>";
		$mail_options["to"] = $recipients;
		$mail_options["text"] = $text;
		$mail_options["html"] = $html;

		// attachments?
		if($attachments) {

			$mail_options["attachment"] = [];

			// array of attachments
			if(is_array($attachments)) {
				foreach($attachments as $attachment) {
					array_push($mail_options["attachment"], ["filePath" => $attachment, "filename" => basename($attachment)]);
				}
			}
			// just one
			else {
				array_push($mail_options["attachment"], ["filePath" => $attachments, "filename" => basename($attachments)]);
			}

		}


		return $this->client->messages()->send($this->domain, $mail_options);

	}


	function sendBulk($_options) {

		$subject = false;
		$text = false;
		$html = false;

		$from_name = false;
		$from_email = false;

		$recipients = false;
		$attachments = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "subject"                : $subject                = $_value; break;
					case "text"                   : $text                   = $_value; break;
					case "html"                   : $html                   = $_value; break;

					case "from_name"              : $from_name              = $_value; break;
					case "from_email"             : $from_email             = $_value; break;
					case "recipients"             : $recipients             = $_value; break;

					case "attachments"            : $attachments            = $_value; break;

				}
			}
		}


		// Update template variable placeholders to Mailgun style
		$html = preg_replace("/{([a-zA-Z0-9_\-]+)}/", "%recipient.$1%", $html);
		$text = preg_replace("/{([a-zA-Z0-9_\-]+)}/", "%recipient.$1%", $text);
		$subject = preg_replace("/{([a-zA-Z0-9_\-]+)}/", "%recipient.$1%", $subject);


		$mail_options = [];

		$mail_options["subject"] = $subject;
		$mail_options["from"] = "$from_name <$from_email>";
		$mail_options["to"] = array_keys($recipients);
		$mail_options["text"] = $text;
		$mail_options["html"] = $html;
		$mail_options["recipient-variables"] = json_encode($recipients);


		// attachments?
		if($attachments) {

			$mail_options["attachment"] = [];

			// array of attachments
			if(is_array($attachments)) {
				foreach($attachments as $attachment) {
					array_push($mail_options["attachment"], ["filePath" => $attachment, "filename" => basename($attachment)]);
				}
			}
			// just one
			else {
				array_push($mail_options["attachment"], ["filePath" => $attachments, "filename" => basename($attachments)]);
			}

		}


		return $this->client->messages()->send($this->domain, $mail_options); 

	}

}
