<?php
/**
* This file contains the site backbone, the Page Class.
* 
* It controls (based on parameters)
* - segment
* - login
* - logoff
* - language
* - dev
*
* Functions:
*
*
*
*
*
*/

/**
* Include base functions and classes
*/

include_once("include/functions.inc.php");

include_once("class/system/message.class.php");
include_once("class/system/session.class.php");

/**
* Site backbone, the Page class
*/
class PageCore {

	public $url;


	// current action - used for access validation
	private $actions;
	private $permissions;


	// page output variables
	public $page_title;
	public $page_description;
	public $body_class;
	public $content_class;



	// DB variables
	private $db_host;
	private $db_username;
	private $db_password;

	// Mailer settings
	private $mail_host;
	private $mail_port;
	private $mail_username;
	private $mail_password;
	private $mail_smtpauth;
	private $mail_secure;
	private $mail_from_email;
	private $mail_from_name;


	/**
	* Get required page information
	*/
	function __construct() {

		// database connection
		@include_once("config/connect_db.php");

		// mailer connection
		@include_once("config/connect_mail.php");


		// set guest user group if no user group is defined (user is not logged in)
		if(!session()->value("user_group_id")) {
			session()->value("user_group_id", 1);
			session()->value("user_id", 1);
			session()->value("csrf", gen_uuid());

		}

//		print session()->value("user_id").", ".session()->value("user_group_id")."<br>";

		// shorthand for clean request uri
		$this->url = str_replace("?".$_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']);


		// login in progress
		if(getVar("login") == "true") {
			$this->logIn();
		}
		// logoff
		if(getVar("logoff") == "true") {
			$this->logOff();
		}
		// set segment
		if(getVar("segment")) {
			$this->segment(getVar("segment"));
		}
		// set language
		if(getVar("language")) {
			$this->language(getVar("language"));
		}
		// set country
		if(getVar("country")) {
			$this->language(getVar("country"));
		}
		// dev mode (dev can be 0)
		if(getVar("dev") !== false) {
			session()->value("dev", getVar("dev"));
		}

		// check access
		$this->setActions(RESTParams());
	}


	// close DB connection when page is done
	function __destruct() {

		global $mysqli_global;
		if($mysqli_global) {
			$mysqli_global->close();
		}
	}


	/**
	* Get page title
	*
	* - fallback to SITE_NAME
	*
	* @return String page title
	*/
	function pageTitle($value = false) {

		// set title
		if($value !== false) {
			$this->page_title = $value;
		}
		// get title
		else {
			// if title already set
			if($this->page_title) {
				return $this->page_title;
			}

			// last resort - use constant
			return SITE_NAME;
		}
	}

	/**
	* Get page description
	*
	* - Fallback to DEFAULT_PAGE_DESCRIPTION, then $this->title
	*
	* @uses Page::title
	* @return String page description
	*/
	function pageDescription($value = false) {
		// set description
		if($value !== false) {
			$this->page_description = $value;
		}
		// get description
		else {
			// if description already set
			if($this->page_description) {
				return $this->page_description;
			}
			// Default page description from config file if available
			else if(defined(DEFAULT_PAGE_DESCRIPTION)) {
				return DEFAULT_PAGE_DESCRIPTION;
			}
			// last resort - use page title
			else {
				return $this->pageTitle();
			}
		}
	}


	/**
	* Get body class
	* this can be sat via page->header
	* 
	* @return String body class
	*/
	function bodyClass($value = false) {
		// set
		if($value !== false) {
			$this->body_class = $value;
		}
		// get
		else {
			// if body_class already set
			if($this->body_class) {
				return $this->body_class;
			}
			else {
				return "";
			}
		}
	}


	/**
	* Get content class
	* this can be sat via page->header
	* 
	* @return String body class
	*/
	function contentClass($value = false) {
		// set
		if($value !== false) {
			$this->content_class = $value;
		}
		// get
		else {
			// if body_class already set
			if($this->content_class) {
				return $this->content_class;
			}
			else {
				return "";
			}
		}
	}


	/**
	* Load external template
	*
	* @param string $template Path to template
	*/
	function template($template, $_options = false) {
		global $HTML;

		if(file_exists(LOCAL_PATH."/templates/".$template)) {
			$file = LOCAL_PATH."/templates/".$template;
		}
		else if(file_exists(FRAMEWORK_PATH."/templates/".$template)) {
			$file = FRAMEWORK_PATH."/templates/".$template;
		}


		$buffer = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "buffer"            : $buffer = $_value; break;
				}
			}
		}


		if(isset($file)) {
			if($buffer) {
//				print "buffering:" . $file;
				ob_start();
				include($file);
				$output = ob_get_contents();
				ob_end_clean();
				return $output;
			}
			else {
				return include($file);
			}
		}
	}

	/**
	* Compile complete page HTML 
	* Render order: templates, header, footer
	* Output order: header, templates, footer
	*
	* @return String page header
	*/
	function page($_options = false) {
		global $HTML;

		$type = "www";
		$templates = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "type"              : $type = $_value; break;

					case "templates"         : $templates = $_value; break;

					case "body_class"        : $this->bodyClass($_value); break;
					case "page_title"        : $this->pageTitle($_value); break;
					case "page_descriptiton" : $this->pageDescription($_value); break;
					case "content_class"     : $this->contentClass($_value); break;
				}
			}
		}

		$_template = "";
		$_header = "";
		$_footer = "";

		if($templates) {
			$templates_array = explode(",", $templates);
			foreach($templates_array as $template) {
//				print "buffering: " . $template;

				$_template .= $this->template($template, array("buffer" => true));

//				print "buffered: " . $_template;
			}
		}

		$_header = $this->header(array("type" => $type, "buffer" => true));
		$_footer = $this->footer(array("type" => $type, "buffer" => true));

		print $_header.$_template.$_footer;
	}


	/**
	* Add page header
	*
	* @return String HTML header or boolean if unbuffered 
	*/
	function header($_options = false) {
		global $HTML;

		$type = "www";
		$buffer = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "buffer"            : $buffer = $_value; break;
					case "type"              : $type = $_value; break;

					case "body_class"        : $this->bodyClass($_value); break;
					case "page_title"        : $this->pageTitle($_value); break;
					case "page_descriptiton" : $this->pageDescription($_value); break;
					case "content_class"     : $this->contentClass($_value); break;
				}
			}
		}

		return $this->template($type.".header.php", array("buffer" => $buffer));

	}

	/**
	* Add page footer
	*
	* @return String HTML footer
	*/
	function footer($_options = false) {
		global $HTML;

		$type = "www";
		$buffer = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "buffer"            : $buffer = $_value; break;
					case "type"              : $type = $_value; break;
				}
			}
		}

//		if($buffer) {
			return $this->template($type.".footer.php", array("buffer" => $buffer));
//		}
//		else {
//			$this->template($type.".footer.php");
//		}
	}


	/**
	* Get/set current language
	*
	* Pass value to set language
	*/
	function language($value = false) {
		// set
		if($value !== false) {
			session()->value("language", $value);
		}
		// get
		else {
			if(!session()->value("language")) {
				session()->value("language", defined("DEFAULT_LANGUAGE_ISO") ? DEFAULT_LANGUAGE_ISO : "DA");
			}
			return session()->value("language");
		}
	}

	/**
	* Get array of available languages
	*/
	function languages() {

		if(!session()->value("languages")) {

			$query = new Query();
			$query->sql("SELECT * FROM ".UT_LANGUAGES);
			session()->value("languages", $query->results());
		}
		return session()->value("languages");
	}

	/**
	* Get/set current country
	*
	* Pass value to set country
	*/
	function country($value = false) {
		// set
		if($value !== false) {
			session()->value("country", $value);
		}
		// get
		else {
			if(!session()->value("country")) {
				session()->value("country", defined("DEFAULT_COUNTRY_ISO") ? DEFAULT_COUNTRY_ISO : "DK");
			}
			return session()->value("country");
		}
	}

	/**
	* Get array of available countries
	*/
	function countries() {

		if(!session()->value("countries")) {

			$query = new Query();
			$query->sql("SELECT * FROM ".UT_COUNTRIES);
			session()->value("countries", $query->results());
		}
		return session()->value("countries");
	}


	/**
	* Get/set current currency
	*
	* Pass value to set currency
	*/
	function currency($value = false) {
		// set
		if($value !== false) {
			session()->value("currency", $value);
		}
		// get
		else {
			if(!session()->value("currency")) {
				$currency_id = defined("DEFAULT_CURRENCY_ISO") ? DEFAULT_CURRENCY_ISO : "DKK";

				$query = new Query();
				if($query->sql("SELECT * FROM ".UT_CURRENCIES." WHERE id = '".$currency_id."'")) {
					$currency = $query->result(0);
				}
//				print_r($currency);

				session()->value("currency", $currency);
			}
			return session()->value("currency");
		}
	}


	/**
	* Access device API and get info about current useragent
	*
	* @return Array Array containing device info, or fallback 
	*/
	// returns currently used browser info to be stored in session
	function segment($value = false) {
		// writeToFile("segment function:" . $value);

		if($value !== false) {
			session()->value("segment", $value);
		}
		else {
			if(!session()->value("segment")) {
				// writeToFile("request new segment:" . $value);

				$device_id = @file_get_contents("http://devices.dearapi.com/xml?ua=".urlencode($_SERVER["HTTP_USER_AGENT"])."&site=".urlencode($_SERVER["HTTP_HOST"]));
		//		$device_id = file_get_contents("http://devices.local/xml?ua=".urlencode($_SERVER["HTTP_USER_AGENT"])."&site=".urlencode($_SERVER["HTTP_HOST"]));
				$device = (array) simplexml_load_string($device_id);
//				print_r($device);

				if($device && isset($device["segment"])) {
					session()->value("segment", $device["segment"]);
				}
				else {
					// offline default value
					session()->value("segment", "desktop");
				}
			}

			return session()->value("segment");
		}

	}

	/**
	* Page actions, security check on controller action level
	*
	* This function is automatically called when controller is loaded
	* The actions are validated and made available to the controller if validation is ok
	*
	* If validation fails, user is redirected to login page
	*
	* The access grants are based on path fragments
	*
	* If a user tries to access /admin/cms/save/product 
	* the system will look for for this full path in access_item of the controller
	* if the full path does not exist, one fragment will be removed until a match is found
	* Thus testing, /admin/cms/save, /admin/cms, /admin, /
	* until a match is found. 
	*
	* If no match is found, no access is granted. Default restriction when access_item is not false!
	*
	* If a match is found, it will be tested in the access table against the current users group access.
	*/
	function setActions($actions=false) {

//		print "setActions:".$actions."<br>\nREQUEST_URI".$_SERVER["REQUEST_URI"]."<br>\nPATH_INFO".$_SERVER["PATH_INFO"]."<br>\n";

		// get $access_item from current controller
		global $access_item;


		// print_r($actions);
		// print "<br>";

		// if controller has access_item setting, perform access validation
		if($access_item && (!defined("SITE_INSTALL") || !SITE_INSTALL)) {

//			print "perform access validation";

			$user_id = session()->value("user_id");
			$user_group_id = session()->value("user_group_id");

			// any access restriction requires a user to be logged in (optionally as Guest - user_group 1, user 1)
			// no need to do any validation if no user_id or user_group_id is found
			if(!$user_id || !$user_group_id) {

				// save current url, to be able to redirect after login
				session()->reset();
				session()->value("login_forward", $this->url);

//				print "no user info";

				header("Location: /login");
				exit();
			}

			// generate appropriate validation_action string from actions to check in database
			// implode actions, prepend / and trailing /
			// get clean controller
			if($actions) {

				$validation_action = "/".implode("/", $actions);

				// access grants should always end with slash
				if(!preg_match("/\/$/", $validation_action)) {
					// if trailing slash is not there, then add it
					$validation_action .= "/";
				}

				// get controller
				$controller = str_replace($_SERVER["PATH_INFO"], "", $_SERVER["REQUEST_URI"]);
			}
			// otherwise empty array
			else {
				// use /
				$validation_action = "/";
				// remove trailing / on controller
				$controller = preg_replace("/\/$/", "", $_SERVER["REQUEST_URI"]);
			}

//			print $controller . " # " . $validation_action . ", " . isset($access_item[$validation_action]) . "\n";

			// look for matching access entry in $access_item
			while(!isset($access_item[$validation_action]) && $validation_action && $validation_action != "/") {
				$validation_action = preg_replace("/[^\/]+\/$/", "", $validation_action);
//				print $validation_action."\n";
			}

//			print $validation_action."\n";

			// no entry found - no access
			if(!isset($access_item[$validation_action])) {
//				print "no access item entry";

				session()->reset();
				session()->value("login_forward", $this->url);
				header("Location: /login");
				exit();
			}
			else {

				// matching access item requires access check
				if($access_item[$validation_action] !== false) {

//					print "check security";
					if(!$this->validateAction($controller.$validation_action)) {
//						print "no db entry";

						session()->reset();
						session()->value("login_forward", $this->url);
						header("Location: /login");
						exit();
					}
				}
			}
		}

		// no access_item in controller - everything is allowed
		// OR validation passed
		$this->actions = $actions;

	}


	// validate action against database permissions
	// on first session validation, get permissions and store in session
	function validateAction($action) {

//		print "validateAction:".$action."<br>";

		if((defined("SITE_INSTALL") && SITE_INSTALL)) {
			return true;
		}


//		global $access_item;

		// remove parameters from $actions string
		$action = preg_replace("/\?.+$/", "", $action);
//		print "action:" . $action . "<br>";
//		print_r($access_item);

		// no access restriction
		// if((!$action && (!$access_item || !$access_item["/"]))) {
		// 	return true;
		// }


		$user_group_id = session()->value("user_group_id");
		$permissions = session()->value("user_group_permissions");


		// if permissions does not exist for this user in this session
		// get user_access for user_group
		if(!$permissions && $user_group_id) {
			$query = new Query();
			$sql = "SELECT action, permission FROM ".SITE_DB.".user_access WHERE user_group_id = ".$user_group_id;
//			print $sql."<br>";
			if($query->sql($sql)) {
				$results = $query->results();
				// parse result in easy queryable structure
				foreach($results as $result) {
					$permissions[$result["action"]] = $result["permission"];
				}

//				print_r($permissions);

				// set controller root access state to "no access" if it does not exist
				// set root permissions for restricted pages without permission setting
				foreach($permissions as $permitted_action => $permission) {

					$parent_action = preg_replace("/[^\/]+\/$/", "", $permitted_action);
//					print $parent_action . ", " . isset($permissions[$parent_action]) . ", " . $permissions[$parent_action] ."\n";

					if(!isset($permissions[$parent_action])) {
						$permissions[$parent_action] = 0;
					}
				}

			}
			session()->value("user_group_permissions", $permissions);
//			print_r($permissions);
		}

 
		if($action) {
			// get actions chuncks
			$chunks = explode("/", preg_replace("/\/$/", "", $action));

			// print "chunks:" . $chunks."<br>";
			// print_r($chunks);
			while($chunks) {

//				print implode("/", $chunks)."/<br>\n";

				if(isset($permissions[implode("/", $chunks)."/"])) {
					if($permissions[implode("/", $chunks)."/"]) {
						return true;
					}
					else {
						return false;
					}
				}
				array_pop($chunks);
			}
		}
		else {
//			print "checking root:" . isset($this->permissions["/"])." && ".$this->permissions["/"]."<br>";
			
			if(isset($permissions["/"]) && $permissions["/"]) {
				return true;
			}
			else {
				return false;
			}
		}

		return false;

	}

	// simple validate action function to determine whether to write out urls for data attributes
	function validAction($action) {
		if($this->validateAction($action)) {
			return $action;
		}
	}

	// validate csrf token
	function validateCsrfToken() {

		// validate csrf-token on all requests?
		if(!(defined("SITE_INSTALL") && SITE_INSTALL)) {

			// if POST, check csrf token
			if($_SERVER["REQUEST_METHOD"] == "POST" &&
				(
					!isset($_POST["csrf-token"]) || 
					!$_POST["csrf-token"] || 
					$_POST["csrf-token"] != session()->value("csrf")
				)
			) {
				// something is fishy, clean up
				unset($_GET);
				unset($_POST);
				unset($_FILES);

				// make sure the user is logged out
				$this->throwOff();

				// notify admin about possible breach attempt
				$this->mail(array(
					"subject" => "CSRF Autorization failed ".SITE_URL, 
					"message" => "CSRF circumvention attempted:".$this->url,
					"template" => "system"
				));
//				message()->addMessage("Autorization failed", array("type" => "error"));
				return false;
			}
			else if($_SERVER["REQUEST_METHOD"] != "POST") {

				return false;

			}
		}

		return true;
	}

	// Get Page actions
	function actions() {
		return $this->actions;
	}


	/**
	* Log in
	*/
	function logIn() {

		$username = getPost("username");
		$password = getPost("password");

		if($username && $password) {
			$query = new Query();

			// make login query
			// look for user with username and password
			$sql = "SELECT users.id as id, users.user_group_id as user_group_id FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames, ".SITE_DB.".user_passwords as passwords WHERE users.status = 1 AND users.id = usernames.user_id AND usernames.user_id = passwords.user_id AND password='".sha1($password)."' AND username='$username'";
//			print $sql;
			if($query->sql($sql)) {

				// add user_id and user_group_id to session
				session()->value("user_id", $query->result(0, "id"));
				session()->value("user_group_id", $query->result(0, "user_group_id"));
				session()->reset("user_group_permissions");

//				session()->value("csrf", gen_uuid());

				if(getPost("ajaxlogin")) {
					$output = new Output();
					$output->screen(array("csrf-token" => session()->value("csrf")));

				}
				else {
					// redirect to originally requested page
					$login_forward = session()->value("login_forward");
					if(!$login_forward || !$this->validateAction($login_forward)) {
						$login_forward = "/";
					}
//					$login_forward = stringOr(session()->value("login_forward"), "/");
//					print "login_forward:" . $login_forward;


					session()->reset("login_forward");

					header("Location: " . $login_forward);
				}
				exit();
			}
		}

		message()->addMessage("Wrong username or password. Try again.", array("type" => "error"));
		return false;
	}


	/**
	* Simple logoff
	* Logoff user and redirect to login page
	*/
	function logOff() {

		$this->addLog("Logoff: ".$user_id);
		//$this->user_id = "";

		session()->reset("user_id");
		session()->reset("user_group_id");
		session()->reset("user_group_permissions");

		session()->reset();

		header("Location: /index.php");
		exit();
	}

	/**
	* Throw off if user is caught on page without permission
	*
	* @param String $url Optional url to forward to after login
	*/
	function throwOff($url=false) {

		// TODO: Compile more information and send in email
		$this->addLog("Throwoff - insufficient privileges:".$this->url." by ". session()->value("user_id"));
		$this->mail(array(
			"subject" => "Throwoff - " . SITE_URL, 
			"message" => "insufficient privileges:".$this->url, 
			"template" => "system"
		));

		//$this->user_id = "";
		session()->reset();
		if($url) {
			session()->value("LoginForward", $url);
		}
		print '<script type="text/javacript">location.href="?page_status=logoff"</script>';
//		header("Location: /index.php");
		exit();
	}


	/**
	* Create database connection
	*/
	function db_connection($settings) {

		// ALTERNATIVE IMPLEMENTATION - USING RECONNECTION WITH EACH QUERY - TOO SLOW
		// global $db;
		// $db["host"] = isset($settings["host"]) ? $settings["host"] : "";
		// $db["username"] = isset($settings["username"]) ? $settings["username"] : "";
		// $db["password"] = isset($settings["password"]) ? $settings["password"] : "";

		$this->db_host = isset($settings["host"]) ? $settings["host"] : "";
		$this->db_username = isset($settings["username"]) ? $settings["username"] : "";
		$this->db_password = isset($settings["password"]) ? $settings["password"] : "";

		$mysqli = new mysqli("".$this->db_host, $this->db_username, $this->db_password);

		if($mysqli->connect_errno) {
		    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
			exit();
		}

		// correct the database connection setting
		$mysqli->query("SET NAMES utf8");
		$mysqli->query("SET CHARACTER SET utf8");
		$mysqli->set_charset("utf8");

		global $mysqli_global;
		$mysqli_global = $mysqli;
	}


	/**
	* Create mailer connection
	*/
	function mail_connection($settings) {

		$this->mail_host = isset($settings["host"]) ? $settings["host"] : "";
		$this->mail_username = isset($settings["username"]) ? $settings["username"] : "";
		$this->mail_password = isset($settings["password"]) ? $settings["password"] : "";
		$this->mail_port = isset($settings["port"]) ? $settings["port"] : "";
		$this->mail_secure = isset($settings["secure"]) ? $settings["secure"] : "";
		$this->mail_smtpauth = isset($settings["smtpauth"]) ? $settings["smtpauth"] : "";
		$this->mail_from_email = isset($settings["from_email"]) ? $settings["from_email"] : "";
		$this->mail_from_name = isset($settings["from_name"]) ? $settings["from_name"] : "";

	}


	/**
	* send mail
	*
	* all parameters in options array structure
	* object can be any type of object providing details for email template
	*/
	function mail($_options = false) {

		$subject = "Mail from ".SITE_URL;
		$message = "";
		$recipients = false;
		$template = false;
		$object = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "recipients" : $recipients = $_value; break;
					case "template"   : $template   = $_value; break;
					case "object"     : $object     = $_value; break;
					case "message"    : $message    = $_value; break;
					case "subject"    : $subject    = $_value; break;
				}
			}
		}

		// if no recipients - send to ADMIN
		if(!$recipients && defined("ADMIN_MAIL")) {
			$recipients = ADMIN_MAIL;
		}
		// include template
		if($template) {
			// include formatting template
			@include("templates/mails/$template.php");
		}

		// only attmempt sending if recipient is specified
		if($message && $recipients) {
			require_once("include/phpmailer/class.phpmailer.php");

			$mail             = new PHPMailer();
			$mail->Subject    = $subject;

			//$mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)

			$mail->CharSet    = "UTF-8";
			$mail->IsSMTP();

			$mail->SMTPAuth   = $this->mail_smtpauth;
			$mail->SMTPSecure = $this->mail_secure;
			$mail->Host       = $this->mail_host;
			$mail->Port       = $this->mail_port;
			$mail->Username   = $this->mail_username;
			$mail->Password   = $this->mail_password;

			$mail->SetFrom($this->mail_from_email, $this->mail_from_name);
			// split comma separated list
			if(!is_array($recipients) && preg_match("/,|;/", $recipients)) {
				$recipients = preg_split("/,|;/", $recipients);
			}
			// multiple entries
			if(is_array($recipients)) {
				foreach($recipients as $recipient) {
					$mail->AddAddress($recipient);
				}
			}
			else {
				$mail->AddAddress($recipients);
			}

			$mail->Body = $message;

			return $mail->Send();
		}

		return false;
	}


	/**
	* Add log entry.
	* Adds user id and user IP along with message and optional values.
	*
	* @param string $message Log message.
	* @param string $collection Log collection.
	*/
	function addLog($message, $collection="framework") {

		$fs = new FileSystem();

		$timestamp = time();
		$user_ip = getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");
		$user_id = session()->value("user_id");

		$log = date("Y-m-d H:i:s", $timestamp). " $user_id $user_ip $message";

		// year-month as folder
		// day as file
		$log_position = LOG_FILE_PATH."/".$collection."/".date("Y/m", $timestamp);
		$log_cursor = LOG_FILE_PATH."/".$collection."/".date("Y/m/Y-m-d", $timestamp);
		$fs->makeDirRecursively($log_position);

		$fp = fopen($log_cursor, "a+");
		fwrite($fp, $log."\n");
		fclose($fp);

	}
	
	
	/**
	* collect message for bundled notification
	* Set collection size in config
	*
	* Automatically formats collection from template (if available) before sending
	*/
	function collectNotification($message, $collection="framework") {

		$fs = new FileSystem();

		$collection_path = LOG_FILE_PATH."/notifications/";
		$fs->makeDirRecursively($collection_path);


		// notifications file
		$collection_file = $collection_path.$collection;


		$timestamp = time();
		$user_ip = getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");
		$user_id = session()->value("user_id");

		$log = date("Y-m-d H:i:s", $timestamp). " $user_id $user_ip $message";

		$fp = fopen($collection_file, "a+");
		fwrite($fp, $log."\n");
		fclose($fp);


		// existing notifications
		$notifications = array();
		if(file_exists($collection_file)) {
			$notifications = file($collection_file);
		}

		// send report and reset collection
		if(count($notifications) >= (defined("SITE_COLLECT_NOTIFICATIONS") ? SITE_COLLECT_NOTIFICATIONS : 10)) {

			$message = implode("\n", $notifications);

			// include formatting template
			@include("templates/mails/notifications/$collection.php");

			// send and reset collection
			if($this->mail(array(
				"subject" => "NOTIFICATION: $collection on ".$_SERVER["SERVER_ADDR"], 
				"message" => $message
			))) {
				$fp = fopen($collection_file, "w");
				fclose($fp);
			}
		}
	}

}

?>
