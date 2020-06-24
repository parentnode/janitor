<?php
/**
* @package janitor.shop
*/

require_once('includes/mailer/mailgun-php-3.0/vendor/autoload.php');

use Mailgun\Mailgun;
use Mailgun\Exception\HttpClientException;


class JanitorMailgun {


	// Mailer settings
	private $api_key;
	private $domain;



	function __construct($_settings) {

		# Instantiate the client.
		$this->domain = $_settings["domain"];
		$this->api_key = $_settings["api-key"];

		// use US endpoint as default
		$this->endpoint = "https://api.mailgun.net";
		
		if(isset($_settings["region"])) {
			
			if(preg_match("/^eu$/i", $_settings["region"])) {
				
				// use EU endpoint
				$this->endpoint = "https://api.eu.mailgun.net";
			}
		}


		$this->client = Mailgun::create($this->api_key, $this->endpoint);

	}

	function send($_options) {

		$subject = false;
		$text = false;
		$html = false;

		$from_name = false;
		$from_email = false;
		$reply_to = false;
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
					case "reply_to"               : $reply_to               = $_value; break;
					case "recipients"             : $recipients             = $_value; break;
					case "cc_recipients"          : $cc_recipients          = $_value; break;
					case "bcc_recipients"         : $bcc_recipients         = $_value; break;


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
		if($reply_to) [$mail_options["h:Reply-To"] = $reply_to];
		$mail_options["to"] = $recipients;
		$mail_options["cc"] = $cc_recipients;
		$mail_options["bcc"] = $bcc_recipients;
		$mail_options["text"] = $text;
		$mail_options["html"] = $html;


		// set tracking 
		if($tracking != "default") {
			if($tracking === false) {
				$tracking = "false";
			}
			elseif($tracking === true) {
				$tracking = "true";
			}
			
			$mail_options["o:tracking"] = $tracking;
		}
		if($track_clicks != "default") {
			if($track_clicks === false) {
				$track_clicks = "false";
			}
			elseif($track_clicks === true) {
				$track_clicks = "true";
			}
			
			$mail_options["o:tracking-clicks"] = $track_clicks;
		}
		if($track_opened != "default") {
			if($track_opened === false) {
				$track_opened = "false";
			}
			elseif($track_opened === true) {
				$track_opened = "true";
			}
			
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

		try {
			return $this->client->messages()->send($this->domain, $mail_options);
		}
		catch(HttpClientException $e) {

			return false;

		}


	}


	function sendBulk($_options) {

		$subject = false;
		$text = false;
		$html = false;

		$from_name = false;
		$from_email = false;
		$reply_to = false;

		$recipients = false;
		$recipient_values = [];
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
					case "reply_to"               : $reply_to               = $_value; break;
					case "recipients"             : $recipients             = $_value; break;
					case "cc_recipients"          : $cc_recipients          = $_value; break;
					case "bcc_recipients"         : $bcc_recipients         = $_value; break;

					case "values"                 : $recipient_values       = $_value; break;

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
		if($reply_to) [$mail_options["h:Reply-To"] = $reply_to];
		
		// CC and BCC recipients currently disabled, since Mailgun handles it differently than we prefer. 
		// We want a CC mail per recipient, Mailgun only provides one CC mail per batch 
		// $mail_options["cc"] = $cc_recipients;
		// $mail_options["bcc"] = $bcc_recipients;
		
		$mail_options["text"] = $text;
		$mail_options["html"] = $html;
		$mail_options["recipient-variables"] = json_encode($recipient_values);

		// set tracking 
		if($tracking != "default") {
			if($tracking === false) {
				$tracking = "false";
			}
			elseif($tracking === true) {
				$tracking = "true";
			}
			
			$mail_options["o:tracking"] = $tracking;
		}
		if($track_clicks != "default") {
			if($track_clicks === false) {
				$track_clicks = "false";
			}
			elseif($track_clicks === true) {
				$track_clicks = "true";
			}
			
			$mail_options["o:tracking-clicks"] = $track_clicks;
		}
		if($track_opened != "default") {
			if($track_opened === false) {
				$track_opened = "false";
			}
			elseif($track_opened === true) {
				$track_opened = "true";
			}
			
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

		try {
			return $this->client->messages()->send($this->domain, $mail_options); 
		}
		catch(HttpClientException $e) {

			return false;

		}

	}

}
