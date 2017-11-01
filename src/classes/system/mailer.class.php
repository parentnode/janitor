<?php
	


class Mailer {


	// Mailer settings
	private $_settings;
	private $adapter;

	/**
	*
	*/
	function __construct() {

		// no adapter selected yet
		$this->adapter = false;

		// mailer connection info
		@include_once("config/connect_mail.php");
			
	}

	function mail_connection($_settings) {

		// set type to default, SMTP, if not defined in configs
		$_settings["type"] = isset($_settings["type"]) ? $_settings["type"] : "smtp";
		$this->_settings = $_settings; 


	}

	function init_adapter() {

		if(!$this->adapter) {

			if(preg_match("/^mailgun$/i", $this->_settings["type"])) {

				@include_once("classes/adapters/mailgun.class.php");
				$this->adapter = new JanitorMailgun($this->_settings);

			}
			// default smtp
			else {

				@include_once("classes/adapters/phpmailer.class.php");
				$this->adapter = new JanitorPHPMailer($this->_settings);

			}

		}

	}

	/**
	* send mail
	*
	* all parameters in options array structure
	* object can be any type of object providing details for email template
	*/
	function send($_options = false) {


		// only load mail adapter when needed
		$this->init_adapter();


		$subject = false;

		// message is appended to template template
		$message = "";
		$template = false;

		$values = [];
		$from_current_user = false;

		$recipients = false;
		$attachments = false;

		$text = "";
		$html = "";


//		$object = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "subject"                : $subject                = $_value; break;

					case "message"                : $message                = $_value; break;
					case "template"               : $template               = $_value; break;

					case "from_current_user"      : $from_current_user      = $_value; break;
					case "values"                 : $values                 = $_value; break;

					case "recipients"             : $recipients             = $_value; break;
					case "attachments"            : $attachments            = $_value; break;

					case "html"                   : $html                   = $_value; break;
					case "text"                   : $text                   = $_value; break;

				}
			}
		}


		// if no recipients - send to ADMIN
		if(!$recipients && defined("ADMIN_EMAIL")) {
			$recipients[] = ADMIN_EMAIL;
		}


		// split comma separated recipient list
		if(!is_array($recipients)) {
			$recipients = preg_split("/,|;/", $recipients);
		}


		// include template
		// template is prioritized before $text and $html
		if($template) {

			// TODO: check for mail template in database
			list($text, $html) = $this->getTemplate($template);

		}
		// No template, text or html, - just plain text message
		else if(!$text && !$html && $message) {
			$text = $message;
		}


		// subject was not specified
		// look for subject in templates - HTML wins
		if(!$subject) {

			// look for subject in html template
			if($html && preg_match("/<title>([^$]+)<\/title>/", $html, $subject_match)) {
				$subject = $subject_match[1];
			}
			// look for subject in text template
			else if($text && preg_match("/^SUBJECT\:([^\n]+)\n/", $text, $subject_match)) {
				$subject = $subject_match[1];
			}
			else {
				$subject = "Mail from ".SITE_URL;
			}

		}


		// remove a subject line from $text template
		$text = preg_replace("/^SUBJECT\:([^\n]+)\n/", "", $text);


		// prepare default values for merging - but don't overwrite
		$values["SITE_URL"] = isset($values["SITE_URL"]) ? $values["SITE_URL"] : SITE_URL;
		$values["SITE_NAME"] = isset($values["SITE_NAME"]) ? $values["SITE_NAME"] : SITE_NAME;
		$values["ADMIN_EMAIL"] = isset($values["ADMIN_EMAIL"]) ? $values["ADMIN_EMAIL"] : ADMIN_EMAIL;
		$values["SITE_EMAIL"] = isset($values["SITE_EMAIL"]) ? $values["SITE_EMAIL"] : SITE_EMAIL;

		// add message to merging array
		if($message && !isset($values["message"])) {
			$values["message"] = $message;
		}

		// Replace values
		foreach($values as $key => $value) {
			$html = preg_replace("/{".$key."}/", $value, $html);
			$text = preg_replace("/{".$key."}/", $value, $text);
			$subject = preg_replace("/{".$key."}/", $value, $subject);
		}


		// if html but no text version
		// create text version from HTML
		if($html && !$text) {

			// Remove subject line from message template
			// Whether it has been used or not, out it must go
//				$text = preg_replace("/^SUBJECT\:([^\n]+)\n/", "", $text);
			$text = strip_tags($html);
			// this is the new message
			$text = trim($text);
		}


		// only attmempt sending if recipients are specified
		if($text && $recipients) {

			list($from_email, $from_name) = $this->getSender($from_current_user);


			return $this->adapter->send([
//			return $mailer->send([
				"subject" => $subject,


				"from_name" => $from_name,
				"from_email" => $from_email,
				"recipients" => $recipients,

				"attachments" => $attachments,
				
				"html" => $html,
				"text" => $text,
			]);


		}

		return false;
	}


	function sendBulk($_options) {

		print "sendBulk";

		// only load mail adapter when needed
		$this->init_adapter();


		$subject = false;

		// message is appended to template template
		$message = "";
		$template = false;

		$values = [];
		$from_current_user = false;

		$recipients = false;
		$attachments = false;

		$text = "";
		$html = "";


//		$object = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "subject"                : $subject                = $_value; break;

					case "message"                : $message                = $_value; break;
					case "template"               : $template               = $_value; break;

					case "from_current_user"      : $from_current_user      = $_value; break;
					case "values"                 : $values                 = $_value; break;

					case "recipients"             : $recipients             = $_value; break;
					case "attachments"            : $attachments            = $_value; break;

					case "html"                   : $html                   = $_value; break;
					case "text"                   : $text                   = $_value; break;

				}
			}
		}



		// include template
		// template is prioritized before $text and $html
		if($template) {

			// TODO: check for mail template in database
			list($text, $html) = $this->getTemplate($template);

		}
		// No template, text or html, - just plain text message
		else if(!$text && !$html && $message) {
			$text = $message;
		}


		// subject was not specified
		// look for subject in templates - HTML wins
		if(!$subject) {

			// look for subject in html template
			if($html && preg_match("/<title>([^$]+)<\/title>/", $html, $subject_match)) {
				$subject = $subject_match[1];
			}
			// look for subject in text template
			else if($text && preg_match("/^SUBJECT\:([^\n]+)\n/", $text, $subject_match)) {
				$subject = $subject_match[1];
			}
			else {
				$subject = "Mail from ".SITE_URL;
			}

		}


		// remove a subject line from $text template
		$text = preg_replace("/^SUBJECT\:([^\n]+)\n/", "", $text);

		// Add system variable to each recipient
		foreach($recipients as $recipient => $values) {

			// prepare default values for merging - but don't overwrite
			$recipients[$recipient]["SITE_URL"] = isset($recipients[$recipient]["SITE_URL"]) ? $values : SITE_URL;
			$recipients[$recipient]["SITE_NAME"] = isset($recipients[$recipient]["SITE_NAME"]) ? $values : SITE_NAME;
			$recipients[$recipient]["ADMIN_EMAIL"] = isset($recipients[$recipient]["ADMIN_EMAIL"]) ? $values : ADMIN_EMAIL;
			$recipients[$recipient]["SITE_EMAIL"] = isset($recipients[$recipient]["SITE_EMAIL"]) ? $values : SITE_EMAIL;

			// add message to merging array
			if($message && !isset($recipients[$recipient]["message"])) {
				$recipients[$recipient]["message"] = $message;
			}

		}


		// if html but no text version
		// create text version from HTML
		if($html && !$text) {

			// Remove subject line from message template
			// Whether it has been used or not, out it must go
//				$text = preg_replace("/^SUBJECT\:([^\n]+)\n/", "", $text);
			$text = strip_tags($html);
			// this is the new message
			$text = trim($text);
		}


		// only attmempt sending if recipients are specified
		if($text && $recipients) {

			list($from_email, $from_name) = $this->getSender($from_current_user);


			return $this->adapter->sendBulk([
//			return $mailer->send([
				"subject" => $subject,


				"from_name" => $from_name,
				"from_email" => $from_email,
				"recipients" => $recipients,

				"attachments" => $attachments,
				
				"html" => $html,
				"text" => $text,
			]);


		}

		return false;

	}


	function getSender($from_current_user) {
		
		// from information
		if($from_current_user) {
			$UC = new User();
			$current_user = $UC->getUser();

			$from_email = $current_user["email"];
			$from_name = $current_user["nickname"];
		}
		else {
			$from_email = (defined("SITE_EMAIL") ? SITE_EMAIL : ADMIN_EMAIL);
			$from_name = SITE_NAME;
		}
		
		return [$from_email, $from_name];
	}

	function getTemplate($template) {
		
		$text = "";
		$html = "";

		// // TEXT template
		// // include local formatting text template
		if(file_exists(LOCAL_PATH."/templates/mails/$template.txt")) {
			$text = file_get_contents(LOCAL_PATH."/templates/mails/$template.txt");
		}
		// include framework formatting text template
		else if(file_exists(FRAMEWORK_PATH."/templates/mails/$template.txt")) {
			$text = file_get_contents(FRAMEWORK_PATH."/templates/mails/$template.txt");
		}
		// or system template
		else {
			@include("templates/mails/$template.txt.php");
		}


		// HTML template
		// include local formatting text template
		if(file_exists(LOCAL_PATH."/templates/mails/$template.html")) {
			$html = file_get_contents(LOCAL_PATH."/templates/mails/$template.html");
		}
		// include framework formatting text template
		else if(file_exists(FRAMEWORK_PATH."/templates/mails/$template.html")) {
			$html = file_get_contents(FRAMEWORK_PATH."/templates/mails/$template.html");
		}
		// or system template
		else {
			ob_start();
			@include("templates/mails/$template.html.php");
			$html = ob_get_contents();
			ob_end_clean();
		}


		return [$text, $html];
	}


}

$mmm = false;

function mailer() {
	global $mmm;
	if(!$mmm) {
		$mmm = new Mailer();

	}
	return $mmm;
}
