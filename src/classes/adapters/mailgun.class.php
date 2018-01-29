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

		// tracking settings
		$tracking = "default";
		$track_clicks = "default";
		$track_opened = "default";


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

					case "tracking"               : $tracking               = $_value; break;
					case "track_clicks"           : $track_clicks           = $_value; break;
					case "track_opened"           : $track_opened           = $_value; break;

				}
			}
		}


		$mail_options = [];

		$mail_options["subject"] = $subject;
		$mail_options["from"] = "$from_name <$from_email>";
		$mail_options["to"] = $recipients;
		$mail_options["text"] = $text;
		$mail_options["html"] = $html;


		// set tracking 
		if($tracking != "default") {
			$mail_options["o:tracking"] = $tracking;
		}
		if($track_clicks != "default") {
			$mail_options["o:tracking-clicks"] = $track_clicks;
		}
		if($track_opened != "default") {
			$mail_options["o:tracking-opens"] = $track_opened;
		}


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

		// tracking settings
		$tracking = "default";
		$track_clicks = "default";
		$track_opened = "default";


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "subject"                : $subject                = $_value; break;
					case "text"                   : $text                   = $_value; break;
					case "html"                   : $html                   = $_value; break;

					case "from_name"              : $from_name              = $_value; break;
					case "from_email"             : $from_email             = $_value; break;
					case "recipients"             : $recipients             = $_value; break;
					case "values"                 : $values                 = $_value; break;

					case "attachments"            : $attachments            = $_value; break;

					case "tracking"               : $tracking               = $_value; break;
					case "track_clicks"           : $track_clicks           = $_value; break;
					case "track_opened"           : $track_opened           = $_value; break;

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
		$mail_options["to"] = $recipients;
		$mail_options["text"] = $text;
		$mail_options["html"] = $html;
		$mail_options["recipient-variables"] = json_encode($values);

		// set tracking 
		if($tracking != "default") {
			$mail_options["o:tracking"] = $tracking;
		}
		if($track_clicks != "default") {
			$mail_options["o:tracking-clicks"] = $track_clicks;
		}
		if($track_opened != "default") {
			$mail_options["o:tracking-opens"] = $track_opened;
		}

//		print_r($mail_options);

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
