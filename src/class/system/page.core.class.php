<?php
/**
* This file contains the site backbone, the Page Class.
*/

header("Content-type: text/html; charset=UTF-8");

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
	public $page_title;
	public $page_description;
	public $body_class;
	
	private $action;
	
	/**
	* Get required page information
	*/
	function __construct() {

		$this->url = str_replace("?".$_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']);

		// check access
		$this->access(RESTParams());


		// login in progress
		if(getVar("login")) {

			// TODO

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
		// dev mode
		if(getVar("dev")) {
			Session::value("dev", getVar("dev"));
		}

		// because I want to gather information about all device-useragents, also on sites not having segmentation implemented in the templates
		$this->segment();

	}


	function language($value = false) {
		// set
		if($value !== false) {
			Session::value("language", $value);
		}
		// get
		else {
			if(!Session::value("language")) {
				Session::value("language", DEFAULT_LANGUAGE_ISO);
			}
			return Session::value("language");
		}
	}

	/**
	* Load external template
	*
	* @param string $template Path to template
	* @param string $template_object Class object to use in template
	* @param string $response_column Column type classname
	* @param string $container_id Id of wrapping container
	* @param string $target_id If template needs to link to other target
	* @param string $silent Get template without getting message (default loud)
	*/
	function template($template) {

		if(file_exists(LOCAL_PATH."/templates/".$template)) {
			$file = LOCAL_PATH."/templates/".$template;
		}
		else if(defined("REGIONAL_PATH") && file_exists(REGIONAL_PATH."/templates/".$template)) {
			$file = REGIONAL_PATH."/templates/".$template;
		}
		else if(defined("GLOBAL_PATH") && file_exists(GLOBAL_PATH."/templates/".$template)) {
			$file = GLOBAL_PATH."/templates/".$template;
		}
		else if(file_exists(FRAMEWORK_PATH."/templates/".$template)) {
			$file = FRAMEWORK_PATH."/templates/".$template;
		}

		if(isset($file)) {
			include($file);
		}
	}

	/**
	* Get page title
	*
	* The page title is complex
	* You can set the title manually via Page::header in your controller
	* If you don't, I will look for, prioritized:
	* - An Item title
	* - Tags - and create a list of them
	* - A Navigation item title
	* - The fallback SITE_NAME
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
			else {
				return SITE_NAME;
			}
		}
	}

	/**
	* Get page description
	* Mostly for page header
	*
	* The page description is complex
	* I will look for, prioritized:
	* - An Item description
	* - Tags - TODO
	* - A Navigation item description - TODO
	* - The fallback $this->title
	*
	* @uses Page::title
	* @uses Page::getObject()
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
	* Add page header
	*
	* @return String HTML header
	*/

	function header($options = false) {
		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "body_class" : $this->bodyClass($value); break;
					case "page_title" : $this->pageTitle($value); break;
					case "page_descriptiton" : $this->pageDescription($value); break;
				}
			}
		}
	
		$this->template("shell.header.php");
	}

	/**
	* Add page footer
	*
	* @return String HTML footer
	*/
	function footer() {
		$this->template("shell.footer.php");
	}


	/**
	* Access device API and get info about current useragent
	*
	* @return Array Array containing device info, or fallback 
	*/
	// returns currently used browser info to be stored in session
	function segment($value = false) {
		if($value !== false) {
			Session::value("segment", $value);
		}
		else {
			if(!Session::value("segment")) {
				$device_id = @file_get_contents("http://devices.dearapi.com/xml?ua=".urlencode($_SERVER["HTTP_USER_AGENT"])."&site=".urlencode($_SERVER["HTTP_HOST"]));
		//		$device_id = file_get_contents("http://devices.local/xml?ua=".urlencode($_SERVER["HTTP_USER_AGENT"])."&site=".urlencode($_SERVER["HTTP_HOST"]));
				$device = (array) simplexml_load_string($device_id);

				if($device && isset($device["segment"])) {
					Session::value("segment", $device["segment"]);
				}
				else {
					// offline default value
					Session::value("segment", "desktop");
				}
			}

			return Session::value("segment");
		}

	}


	/**
	* Get page status
	*
	* @param String $action action parameter to check for in status (status can be combined page,list)
	* @return bool Page status
	*/
	function access($action = false) {

		// TODO: Security check on action - only required accesscheck, bacause all requests load page and page checks makes this call

		if($action) {
			$this->action = $action;
		}
		else {
			return $this->action;
		}
	}


	/**
	* Set page status
	*
	* @param string|bool $status Page status
	*/
	function setStatus($status){
		// if(!Secity::hasAccess($status)) {
		// 	$this->throwOff($_SERVER["REQUEST_URI"]);
		// }
		// else {
			$this->status = $status;
		// }
	}

	/**
	* Simple logoff
	* Logoff user and redirect to login page
	*/
	function logOff() {
		$this->addLog("Logoff ". UT_USE);
		//$this->user_id = "";
		Session::reset();
		header("Location: /index.php");
		exit();
	}

	/**
	* Throw off if user is caught on page without permission
	*
	* @param String $url Optional url to forward to after login
	*/
	function throwOff($url=false) {
		$this->addLog("Login - insufficient privileges:".$this->url." ". UT_USE);
		//$this->user_id = "";
		Session::resetLogin();
		if($url) {
			Session::setLoginForward($url);
		}
		print "<script>location.href='?page_status=logoff'</script>";
//		header("Location: /index.php");
		exit();
	}



	/**
	* Notify admin of a problem
	*
	* @param string $message Notification
	*/
	function notifyAdmin($message) {
		$message = $message."\n\nfile:".$this->url;
		mail("martin@think.dk", "SERVER NOTICE", $message);
	}


	/**
	* Add log entry.
	* Adds user id and user IP along with message and optional values.
	*
	* @param string $message Log message.
	* @return bool Success
	*/
	function addLog($message) {
		$timestamp = time();

		if(Session::getLogin()) {
			$user_id = Session::getLogin()->getUserId();
			$user_ip = Session::getLogin()->getUserIp();
		}
		else {
			$user_id = "N/A";
			$user_ip = "N/A";
		}

		$log = date("Y-m-d H:i:s", $timestamp). " $user_id $user_ip $message";

		// year-month as folder
		// day as file
		$log_position = LOG_FILE_PATH."/framework/".date("Y/m", $timestamp);
		$log_cursor = LOG_FILE_PATH."/framework/".date("Y/m/Y-m-d", $timestamp);
		FileSystem::makeDirRecursively($log_position);

		$fp = fopen($log_cursor, "a+");
		fwrite($fp, $log."\n");
		fclose($fp);

	}


}

?>