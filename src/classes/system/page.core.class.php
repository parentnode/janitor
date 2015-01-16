<?php

// Include base functions and classes
include_once("includes/functions.inc.php");

include_once("classes/system/message.class.php");
include_once("classes/system/session.class.php");


/**
* This class contains the site backbone
* 
* It controls (based on parameters)
* - segment
* - login
* - logoff
* - language
* - dev
*/
class PageCore {

	public $url;


	// current action - used for access validation
	private $actions;
	private $permissions;


	// page output variables
	public $page_title;
	public $page_description;
	public $page_image;

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


	/**
	* Get required page information
	*/
	function __construct() {

		// database connection
		$this->loadDBConfiguration();

		// mailer connection
		$this->loadMailConfiguration();


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
	* Set OG meta data from $item
	*
	* Facebook OG metadata tags
	*
	*/
	function sharingMetaData($item = false, $_options = false) {


		if($item !== false) {

			$description_index = "description";
			$title_index = "name";
			$image_index = "mediae";

			if($_options !== false) {
				foreach($_options as $_option => $_value) {
					switch($_option) {
						case "description"       : $description_index = $_value; break;
						case "title"             : $title_index = $_value; break;
						case "image"             : $image_index = $_value; break;
					}
				}
			}


			if(isset($item[$title_index])) {
				$this->pageTitle($item[$title_index]);
			}

			if(isset($item[$description_index])) {
				$this->pageDescription($item[$description_index]);
			}

			if(isset($item[$image_index]) && $item[$image_index]) {
				foreach($item[$image_index] as $image) {
					if(preg_match("/jpg|png/", $image["format"])) {

						// Facebook size
						if(strpos($_SERVER["HTTP_USER_AGENT"], "facebookexternalhit") !== false) {
							$this->pageImage("/images/".$item["id"]."/".$image["variant"]."/1200x630.".$image["format"]);
						}
						// Google Plus size
						else if(strpos($_SERVER["HTTP_USER_AGENT"], "Google") !== false) {

							// Google will not accept Janitors image generation on the fly method
							// pregenerate for google
							$image_parts = $item["id"]."/".$image["variant"]."/300x300.".$image["format"];
							if(!file_exists(PUBLIC_FILE_PATH."/".$image_parts)) {
								file_get_contents(SITE_URL."/images/".$image_parts);
							}

							$this->pageImage("/images/".$image_parts);

						}
						// Linkedin size
						else if(strpos($_SERVER["HTTP_USER_AGENT"], "LinkedInBot") !== false) {
							$this->pageImage("/images/".$item["id"]."/".$image["variant"]."/180x110.".$image["format"]);
						}
						// Standard size for everyone else
						else {
							$this->pageImage("/images/".$item["id"]."/".$image["variant"]."/250x.jpg");
						}

						break;
					}
				}
			}

		}
		else {

			$_ = '';

			$_ .= '<meta property="og:title" content="'.$this->pageTitle().'" />';
			$_ .= '<meta property="og:description" content="'.$this->pageDescription().'" />';
			$_ .= '<meta property="og:image" content="'.SITE_URL.$this->pageImage().'" />';
			$_ .= '<meta property="og:url" content="'.SITE_URL.$this->url.'" />';

			return $_;
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
	* @uses Page::pageTitle
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
			else if(defined("DEFAULT_PAGE_DESCRIPTION")) {
				return DEFAULT_PAGE_DESCRIPTION;
			}
			// last resort - use page title
			else {
				return $this->pageTitle();
			}
		}
	}

	/**
	* Get page image
	*
	* - Fallback to DEFAULT_PAGE_IMAGE
	*
	* @return String page image
	*/
	function pageImage($value = false) {
		// set description
		if($value !== false) {
			$this->page_image = $value;
		}
		// get description
		else {
			// if description already set
			if($this->page_image) {
				return $this->page_image;
			}
			// Default page description from config file if available
			else if(defined("DEFAULT_PAGE_IMAGE")) {
				return DEFAULT_PAGE_IMAGE;
			}

			// last resort favicon
			return "/favicon.png";
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
		global $JML;

		$buffer = false;
		$error = "pages/404.php";

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "buffer"            : $buffer = $_value; break;
					case "error"             : $error = $_value; break;
				}
			}
		}

		if(file_exists(LOCAL_PATH."/templates/".$template)) {
			$file = LOCAL_PATH."/templates/".$template;
		}
		else if(file_exists(FRAMEWORK_PATH."/templates/".$template)) {
			$file = FRAMEWORK_PATH."/templates/".$template;
		}

		// template was not found - include error template
		else if(file_exists(LOCAL_PATH."/templates/".$error)) {
			$file = LOCAL_PATH."/templates/".$error;
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

		return false;
	}


	/**
	* Compile complete page HTML 
	* Render order: templates, header, footer
	* Output order: header, templates, footer
	*
	* TODO: consider implementing 404 response code for 404 template - http_response_code(404);
	*
	* @return String page header
	*/
	function page($_options = false) {
		global $HTML;
		global $JML;

		$type = "www";
		$templates = false;
		$error = "pages/404.php";

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "type"              : $type       = $_value; break;

					case "templates"         : $templates  = $_value; break;

					case "error"             : $error      = $_value; break;

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

				$_template .= $this->template($template, array("buffer" => true, "error" => $error));

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
		global $JML;

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
		global $JML;

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
				session()->value("language", defined("DEFAULT_LANGUAGE_ISO") ? DEFAULT_LANGUAGE_ISO : "EN");
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
	*
	* @return Array containing currency info 
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
	* @return Array containing device info, or fallback 
	*/
	// returns currently used browser info to be stored in session
	function segment($value = false) {
		// writeToFile("segment function:" . $value);

		if($value !== false) {
			if(is_string($value) && preg_match("/^(basic|desktop|desktop_ie|desktop_light|tablet|mobile|mobile_touch|mobile_light|tv)$/", $value)) {
				session()->value("segment", $value);
			}
		}
		else {
			if(!session()->value("segment")) {
				// writeToFile("request new segment:" . $value);

				$device_id = @file_get_contents("http://detector.dearapi.com/xml?ua=".urlencode($_SERVER["HTTP_USER_AGENT"])."&site=".urlencode($_SERVER["HTTP_HOST"]));
		//		$device_id = file_get_contents("http://detector.api/xml?ua=".urlencode($_SERVER["HTTP_USER_AGENT"])."&site=".urlencode($_SERVER["HTTP_HOST"]));
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
	* Page actions, security check on controller access
	*
	* This function is automatically called when controller is accessed and $page is instanciated
	* The actions are validated and made available to the controller if validation is ok
	*
	*
	* The access grants are based on path fragments
	*
	* If a user tries to access /janitor/admin/items/save/product 
	* the system will split this path into controller part and action part
	* controller part: /janitor/admin/items
	* action part: /save/product
	*
	* Controller and action will be validation using checkPermissions
	* 
	* If validation fails, user is redirected to login page
	*
	* @param $actions Array of actions sent to current controller
	*/
	function setActions($actions) {

		// get $access_item from current controller
		global $access_item;

		// if controller has access_item setting, perform access validation
		if($access_item) {

			// convert actions to path string to align logic with validatePath
			$action = "/".implode("/", $actions);
			// if trailing slash is there, then remove it
			$action = preg_replace("/\/$/", "", $action);


			// identify controller
			$controller = preg_replace("/\.php$/", "", $_SERVER["SCRIPT_NAME"]);


			// check permissions
			if(!$this->checkPermissions($controller, $action, $access_item)) {

				session()->reset();

				// save current url, to be able to redirect after login
				session()->value("login_forward", $this->url);

				// redirect to login
				header("Location: /login");
				exit();

			}
		}

		// access_item is false in controller
		// SITE_INSTALL
		// OR validation passed
		// -  access is allowed
		$this->actions = $actions;

	}


	/**
	* Validate access permission for full /controller/action path
	* Used to check if a path is valid when generating links, form actions, etc
	*
	* @param $path String containing full /controller/action path
	* @return boolean Allowed or not
	*/
	function validatePath($path) {

//		print "validatePath:".$path."<br>\n";

		// remove GET parameters from $actions string
		$path = preg_replace("/\?.+$/", "", $path);
		// remove trailing slash
		$path = preg_replace("/\/$/", "", $path);

		// add index to our testing path to catch root controllers (index.php)
		$test_path = $path."/index";


		// create fragments array for controller identification
		$controller = false;
		$fragments = explode("/", $test_path);

		// loop through fragments while removing one fragment in each loop until only one fragment exists
		while($fragments) {

			// create new /controller/action path to check in permissions array
			$path_test = implode("/", $fragments);

			// make theoretic controller path to test
			// if path contains /janitor/admin it is a janitor core controller
			if(preg_match("/^\/janitor\/admin/", $path_test)) {
				$controller_test = FRAMEWORK_PATH."/www".preg_replace("/^\/janitor\/admin/", "", $path_test).".php";
			}
			// path could be setup script
			else if(preg_match("/^\/setup/", $path_test)) {
				$controller_test = FRAMEWORK_PATH."/".$path_test.".php";
			}
			// local controller
			else {
				$controller_test = LOCAL_PATH."/www".$path_test.".php";
			}

//			print "controller_test:" . $controller_test . "<br>\n";


			// does controller exist
			if(file_exists($controller_test)) {

				// controller is found
				$controller = $path_test;

				// read access_item of controller
				$read_access = true;
				include($controller_test);

				// end while loop
				break;
			}

			// controller is still not found, pop another fragment off
			array_pop($fragments);
		}


		// both controller and access_item is found
		if($controller && isset($access_item)) {

			// deduce action
			$action = str_replace($controller, "", $path);

			// check permissions
			return $this->checkPermissions($controller, $action, $access_item);
		}


//		print "no controller or access_item found<br>\n";
		// no controller or access_item found
		return false;
	}


	/**
	* Access permission check
	*
	* If access_item is false, access is granted
	*
	* On first session validation, get permissions and store in session to avoid excessive DB lookups
	*
	* Iterate action to find match in access_item of the controller
	* If the full action does not exist, one fragment will be removed until a match is found
	*
	* If no match is found, no access is granted. Default restriction when access_item is not false!
	* If no user_group is present, no access is granted.
	*
	* If a access_item is set for path, it will be tested in the access table against the 
	* current users group access.
	*
	* @param String $controller controller to check permissions for
	* @param String $action action to check permissions for
	* @param String $access_item access_item of controller
	* @return boolean Allowed or not
	*/
	function checkPermissions($controller, $action, $access_item) {


//		print "controller:" . $controller . "<br>\n";
//		print "action:" . $action . "<br>\n";
//		print_r($access_item);
//		print "<br>\n";


		// all actions are allowed on SITE_INSTALL
		if((defined("SITE_INSTALL") && SITE_INSTALL)) {
//			print "all good";
			return true;
		}

		// no access restrictions
		if($access_item === false) {
			return true;
		}


		// get actions fragments as array to make it easier to remove fragments
		// first index in fragments will be empty to indicate controller root
		$fragments = explode("/", $action);


		// loop through fragments while removing one fragment in each loop until only one fragment exists
		while($fragments) {

			// create new /controller/action path to check in permissions array
			$action_test = implode("/", $fragments);

			// does actions test exist in access_item
			if(isset($access_item[$action_test])) {

				// check if access_item points to other access_item
				if($access_item[$action_test] !== true && $access_item[$action_test] !== false) {
					$action_test = $access_item[$action_test];
				}

				// end while loop
				break;
			}

			// /controller/action/ path not found - remove a fragment and try again
			array_pop($fragments);
		}

		// action should be at least a slash
		$action_test = $action_test ? $action_test : "/";


		// no entry found in access_item while iteration action fragments
		// must be an illegal controller/action path
		// - deny access
		if(!isset($access_item[$action_test])) {
//			print "no access item entry<br>\n";

			return false;
		}
		// no access restrictions for this action
		else if($access_item[$action_test] === false) {
//			print "no restriction<br>\n";

			return true;
		}
		// matching access item requires access check
		else {

			// get group and permissions from session
			$user_group_id = session()->value("user_group_id");
			$permissions = session()->value("user_group_permissions");

//			print "group: ".$user_group_id."<br>\n";

			// TEMP
			$permissions = false;

			// any access restriction requires a user to be logged in (optionally as Guest - user_group 1, user 1)
			// no need to do any validation if no user_group_id is found
			if(!$user_group_id) {
//				print "no group<br>\n";

				return false;
			}

			// if permissions does not exist for this user in this session
			// this requires a database lookup - result is stored in session to 
			// get user_access for user_group
			else if(!$permissions) {

				$query = new Query();
				$sql = "SELECT controller, action, permission FROM ".SITE_DB.".user_access WHERE user_group_id = ".$user_group_id;
	//			print $sql."<br>\n";

				if($query->sql($sql)) {
					$results = $query->results();

					// parse result in easy queryable structure
					// $permission[controller][action] = 1
					foreach($results as $result) {
						$permissions[$result["controller"]][$result["action"]] = $result["permission"];
					}

				}

				// store permissions in session
				session()->value("user_group_permissions", $permissions);
			}

			// do the actual access check
			if(isset($permissions[$controller]) && isset($permissions[$controller][$action_test]) && $permissions[$controller][$action_test]) {
//				print "!1!<br>\n";
				return true;
			}

		}

//		print "everything failed<br>\n";
		return false;
	}


	// simple validate action function to determine whether to write out urls for data attributes
	function validPath($path) {
		if($this->validatePath($path)) {
			return $path;
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

				// set new csrf token for user
				session()->value("csrf", gen_uuid());

				if(getPost("ajaxlogin")) {
					$output = new Output();
					$output->screen(array("csrf-token" => session()->value("csrf")));

				}
				else {
					// redirect to originally requested page
					$login_forward = session()->value("login_forward");
					if(!$login_forward || !$this->validatePath($login_forward)) {
						$login_forward = "/";
					}

					session()->reset("login_forward");

					header("Location: " . $login_forward);
				}
				exit();
			}
		}

		message()->addMessage("Computer says NO!", array("type" => "error"));
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
		print '<script type="text/javacript">location.href="/login?page_status=logoff"</script>';

		header("Location: /login");

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

			global $mysqli_global;
			$mysqli_global = false;

			// connection error is handled different when setting up site
			if(!defined("SETUP_TYPE")) {

				echo "Failed to connect to MySQL: " . $mysqli->connect_error;
				exit();

			}

			return;
		}

		// correct the database connection setting
		$mysqli->query("SET NAMES utf8");
		$mysqli->query("SET CHARACTER SET utf8");
		$mysqli->set_charset("utf8");

		global $mysqli_global;
		$mysqli_global = $mysqli;
	}
	// DB connection loader
	function loadDBConfiguration() {
		// database connection
		@include_once("config/connect_db.php");
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

	}
	// Mail connection loader
	function loadMailConfiguration() {
		// mailer connection
		@include_once("config/connect_mail.php");
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
		if(!$recipients && defined("ADMIN_EMAIL")) {
			$recipients = ADMIN_EMAIL;
		}
		// include template
		if($template) {
			// include formatting template
			@include("templates/mails/$template.php");
		}

		// only attmempt sending if recipient is specified
		if($message && $recipients) {
			require_once("includes/phpmailer/class.phpmailer.php");

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

			$from = (defined("SITE_EMAIL") ? SITE_EMAIL : ADMIN_EMAIL);

			$mail->addReplyTo($from, SITE_NAME);

			$mail->SetFrom($from, SITE_NAME);
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
