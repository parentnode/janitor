<?php
	


class MailGateway {


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

			if($this->_settings && preg_match("/^mailgun$/i", $this->_settings["type"])) {

				@include_once("classes/adapters/mailer/mailgun.class.php");
				$this->adapter = new JanitorMailgun($this->_settings);

			}
			// default smtp
			else {

				@include_once("classes/adapters/mailer/phpmailer.class.php");
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

		// Only attempt sending with valid adapter
		if($this->adapter && defined("ADMIN_EMAIL")) {

			$subject = false;

			// message is appended to template template
			$message = "";
			$template = false;

			$values = [];
			$from_current_user = false;
			$from_name = false;
			$from_email = false;
			$reply_to = false;

			// we'll do some extra recipient checking before making the final recipients list
			$temp_recipients = false;
			$temp_cc_recipients = false;
			$temp_bcc_recipients = false;

			$attachments = false;

			$text = "";
			$html = "";


			// tracking settings - should always use default settings from mail-service, unless explicitly set
			$tracking = "default";
			$track_clicks = "default";
			$track_opened = "default";

	//		$object = false;

			if($_options !== false) {
				foreach($_options as $_option => $_value) {
					switch($_option) {

						case "subject"                : $subject                = $_value; break;

						case "message"                : $message                = $_value; break;
						case "template"               : $template               = $_value; break;

						case "from_name"              : $from_name              = $_value; break;
						case "from_email"             : $from_email             = $_value; break;
						case "from_current_user"      : $from_current_user      = $_value; break;
						case "reply_to"               : $reply_to               = $_value; break;
						case "values"                 : $values                 = $_value; break;

						case "recipients"             : $temp_recipients        = $_value; break;
						case "cc_recipients"          : $temp_cc_recipients     = $_value; break;
						case "bcc_recipients"         : $temp_bcc_recipients    = $_value; break;
						case "attachments"            : $attachments            = $_value; break;

						case "html"                   : $html                   = $_value; break;
						case "text"                   : $text                   = $_value; break;

						// tracking only supported if supported by mailservice
						case "tracking"               : $tracking               = $_value; break;
						case "track_clicks"           : $track_clicks           = $_value; break;
						case "track_opened"           : $track_opened           = $_value; break;

					}
				}
			}


			// if no recipients - send to ADMIN
			if(!$temp_recipients && defined("ADMIN_EMAIL")) {
				$temp_recipients = [];
				$temp_recipients[] = ADMIN_EMAIL;
			}

			$recipients = $this->getRecipients($temp_recipients);
			$cc_recipients = $this->getRecipients($temp_cc_recipients);
			$bcc_recipients = $this->getRecipients($temp_bcc_recipients);

			
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
			// trim text message
			$text = trim($text);


			// prepare default values for merging - but don't overwrite
			$values["SITE_URL"] = isset($values["SITE_URL"]) ? $values["SITE_URL"] : SITE_URL;
			$values["SITE_SIGNUP_URL"] = isset($values["SITE_SIGNUP_URL"]) ? $values["SITE_SIGNUP_URL"] : SITE_SIGNUP_URL;
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

				// create DOM object from HTML string
				$dom = DOM()->createDOM($html);

				// get formatted text string from DOM object
				$text = DOM()->getFormattedTextFromDOM($dom);

				//cleanup
				$DC = null;
				$dom = null;

			}


			// only attempt sending if recipients are specified
			if($text && $recipients) {

				list($from_email, $from_name) = $this->getSender($from_name, $from_email, $from_current_user);


				return $this->adapter->send([
	//			return $mailer->send([
					"subject" => $subject,


					"from_name" => $from_name,
					"from_email" => $from_email,
					"reply_to" => $reply_to,
					"recipients" => $recipients,
					"cc_recipients" => $cc_recipients,
					"bcc_recipients" => $bcc_recipients,

					"attachments" => $attachments,

					"html" => $html,
					"text" => $text,

					"tracking" => $tracking,
					"track_clicks" => $track_clicks,
					"track_opened" => $track_opened,

				]);

			}

		}

		return false;
	}


	function sendBulk($_options) {

//		print "sendBulk<br>\n";

		// only load mail adapter when needed
		$this->init_adapter();

		// Only attempt sending with valid adapter
		if($this->adapter && defined("ADMIN_EMAIL")) {

			$subject = false;

			// message is appended to template template
			$message = "";
			$template = false;

			$values = [];
			$from_current_user = false;
			$from_name = false;
			$from_email = false;
			$reply_to = false;

			$temp_recipients = false;
			$temp_cc_recipients = false;
			$temp_bcc_recipients = false;
			
			$attachments = false;

			$text = "";
			$html = "";


			// tracking settings - should always use default settings from mail-service, unless explicitly set
			$tracking = "default";
			$track_clicks = "default";
			$track_opened = "default";

	//		$object = false;

			if($_options !== false) {
				foreach($_options as $_option => $_value) {
					switch($_option) {

						case "subject"                : $subject                = $_value; break;

						case "message"                : $message                = $_value; break;
						case "template"               : $template               = $_value; break;

						case "from_name"              : $from_name              = $_value; break;
						case "from_email"             : $from_email             = $_value; break;
						case "from_current_user"      : $from_current_user      = $_value; break;
						case "reply_to"               : $reply_to               = $_value; break;
						case "values"                 : $values                 = $_value; break;

						case "recipients"             : $temp_recipients        = $_value; break;
						case "cc_recipients"          : $temp_cc_recipients     = $_value; break;
						case "bcc_recipients"         : $temp_bcc_recipients    = $_value; break;
						case "attachments"            : $attachments            = $_value; break;

						case "html"                   : $html                   = $_value; break;
						case "text"                   : $text                   = $_value; break;

						// tracking only supported if supported by mailservice
						case "tracking"               : $tracking               = $_value; break;
						case "track_clicks"           : $track_clicks           = $_value; break;
						case "track_opened"           : $track_opened           = $_value; break;

					}
				}
			}


			$recipients = $this->getRecipients($temp_recipients);
			$cc_recipients = $this->getRecipients($temp_cc_recipients);
			$bcc_recipients = $this->getRecipients($temp_bcc_recipients);


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

	//		print_r($html);

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
			// trim text message
			$text = trim($text);

	//		print_r($recipients);

			// Add system variable to each recipient
	//		print_r($values);
			foreach($recipients as $recipient) {

				// prepare default values for merging - but don't overwrite
				$values[$recipient]["SITE_URL"] = isset($values[$recipient]["SITE_URL"]) ? $values[$recipient]["SITE_URL"] : SITE_URL;
				$values[$recipient]["SITE_SIGNUP_URL"] = isset($values[$recipient]["SITE_SIGNUP_URL"]) ? $values[$recipient]["SITE_SIGNUP_URL"] : SITE_SIGNUP_URL;
				$values[$recipient]["SITE_NAME"] = isset($values[$recipient]["SITE_NAME"]) ? $values[$recipient]["SITE_NAME"] : SITE_NAME;
				$values[$recipient]["SITE_EMAIL"] = isset($values[$recipient]["SITE_EMAIL"]) ? $values[$recipient]["SITE_EMAIL"] : SITE_EMAIL;
				$values[$recipient]["ADMIN_EMAIL"] = isset($values[$recipient]["ADMIN_EMAIL"]) ? $values[$recipient]["ADMIN_EMAIL"] : ADMIN_EMAIL;

				// add message to merging array
				if($message && !isset($values[$recipient]["message"])) {
					$values[$recipient]["message"] = $message;
				}
			}


			// if html but no text version
			// create text version from HTML
			if($html && !$text) {

				// create DOM object from HTML string
				$dom = DOM()->createDOM($html);

				// get formatted text string from DOM object
				$text = DOM()->getFormattedTextFromDOM($dom);


	//			print $text;
				//cleanup
				$DC = null;
				$dom = null;
				// $text = strip_tags($html);
				// // this is the new message
				// $text = trim($text);
			}


			// only attempt sending if recipients are specified
			if($text && $recipients) {

				list($from_email, $from_name) = $this->getSender($from_name, $from_email, $from_current_user);

				return $this->adapter->sendBulk([
	//			return $mailer->send([
					"subject" => $subject,


					"from_name" => $from_name,
					"from_email" => $from_email,
					"reply_to" => $reply_to,
					"recipients" => $recipients,
					"cc_recipients" => $cc_recipients,
					"bcc_recipients" => $bcc_recipients,
					"values" => $values,

					"attachments" => $attachments,
				
					"html" => $html,
					"text" => $text,

					"tracking" => $tracking,
					"track_clicks" => $track_clicks,
					"track_opened" => $track_opened,
				]);

			}

		}

		return false;

	}


	function getSender($from_name, $from_email, $from_current_user) {

		// from information
		if($from_current_user) {
			$UC = new User();
			$current_user = $UC->getUser();
			
			if($current_user) {
				if(!$from_name) {
					$from_name = $current_user["nickname"];
				}
				if(!$from_email) {
					$from_email = $current_user["email"];
				}
			}
		}
		else {
			if(!$from_name) {
				$from_name = SITE_NAME;
			}
			if(!$from_email) {
				$from_email = (defined("SITE_EMAIL") ? SITE_EMAIL : ADMIN_EMAIL);
			}
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

	function getRecipients($temp_recipients) {
		
		$recipients = [];

		// split comma separated recipient list
		if(!is_array($temp_recipients)) {
			$temp_recipients = preg_split("/,|;/", $temp_recipients);
		}

		// check that recipient seems to be a valid email
		foreach($temp_recipients as $recipient) {
			// only use valid recipients
			if($recipient && preg_match("/^[\w\.\-_\+]+@[\w\-\.]+\.\w{2,10}$/", $recipient)) {
				$recipients[] = $recipient;
			}
		}

		return $recipients;
	}

}
