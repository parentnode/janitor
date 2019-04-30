<?php

// Include base functions and classes
include_once("includes/functions.inc.php");

include_once("classes/system/message.class.php");
include_once("classes/system/session.class.php");
include_once("classes/system/cache.class.php");





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
	public $header_includes;



	// DB variables
	private $db_host;
	private $db_username;
	private $db_password;



	/**
	* Get required page information
	*/
	function __construct() {

		// database connection
		$this->loadDBConfiguration();


		// set guest user group if no user group is defined (user is not logged in)
		if(!session()->value("user_group_id")) {
			session()->value("user_group_id", 1);
			session()->value("user_id", 1);
			session()->value("csrf", gen_uuid());
			session()->value("site", SITE_URL);
			session()->value("ip", (getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR")));
			session()->value("useragent", ((isset($_SERVER["HTTP_USER_AGENT"]) && $_SERVER["HTTP_USER_AGENT"]) ? stripslashes($_SERVER["HTTP_USER_AGENT"]) : "Unknown"));
			session()->value("last_login_at", date("Y-m-d H:i:s"));
		}

//		print session()->value("user_id").", ".session()->value("user_group_id")."<br>";

		// shorthand for clean request uri
		$this->url = str_replace("?".$_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']);


		// login in progress
		if(getVar("login") == "true") {
			$this->logIn();
		}
		// login in progress
		if(getVar("token")) {
			$this->tokenLogIn();
		}
		// logoff
		if(getVar("logoff") == "true") {
			$this->logOff();
		}

		// set segment
		if(getVar("segment")) {
			// set real segment value
			$this->segment(array("value" => getVar("segment"), "type" => "segment"));
		}

		// set language
		if(getVar("language")) {
			$this->language(getVar("language"));
		}
		// set country
		if(getVar("country")) {
			$this->country(getVar("country"));
		}
		// set country
		if(getVar("currency")) {
			$this->currency(getVar("currency"));
		}

		// dev mode (dev can be 0)
		if(getVar("dev") !== false) {
			session()->value("dev", getVar("dev"));
		}

		// check access
		$this->setActions(RESTParams());

//		session()->flush("test");
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
						if(isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], "facebookexternalhit") !== false) {
							$this->pageImage("/images/".$item["id"]."/".$image["variant"]."/1200x630.".$image["format"]);
						}
						// Google Plus size
						else if(isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], "Google") !== false) {

							// Google will not accept Janitors image generation on the fly method
							// pregenerate for google
							$image_parts = $item["id"]."/".$image["variant"]."/300x300.".$image["format"];
							if(!file_exists(PUBLIC_FILE_PATH."/".$image_parts)) {
								file_get_contents(SITE_URL."/images/".$image_parts);
							}

							$this->pageImage("/images/".$image_parts);

						}
						// Linkedin size
						else if(isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], "LinkedInBot") !== false) {
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
	* Get/set page title
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
				return strip_tags($this->page_title);
			}

			// last resort - use constant
			return SITE_NAME;
		}
	}

	/**
	* Get/set page description
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
	* Get/set page image
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
	* Get/set body class
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
	* Get/set content class
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
	* Get/set header includes
	* 
	* @param Array $files to be included
	* @return String of include statements
	*/
	function headerIncludes($files = false) {
		// set
		if($files !== false) {
			// add files to include list (includes can be added more than once)
			if(!$this->header_includes) {
				$this->header_includes = array();
			}
			// add $files to header_includes array
			$this->header_includes = array_merge($this->header_includes, $files);
		}
		// get
		else {
			// loop through header_includes and create correct include statements
			if($this->header_includes) {
				$_ = "";
				foreach($this->header_includes as $include) {
					if(preg_match("/\.js$/", $include)) {
						$_ .= '<script type="text/javascript" src="'.$include.'"></script>';
					}
					else if(preg_match("/\.css$/", $include)) {
						$_ .= '<link type="text/css" rel="stylesheet" media="all" href="'.$include.'" />';
					}
				}

				return $_."\n";
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

		// template was not found - include local error template
		else if(file_exists(LOCAL_PATH."/templates/".$error)) {
			$file = LOCAL_PATH."/templates/".$error;
		}
		// template was not found - include framework error template
		else if(file_exists(FRAMEWORK_PATH."/templates/".$error)) {
			$file = FRAMEWORK_PATH."/templates/".$error;
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
	* Get/set current user language
	*
	* Pass value to set language
	*
	* @return language ISO id on get
	*/
	function language($value = false) {
		// set
		if($value !== false) {
			$query = new Query();
			// only allow valid language
			// look for language in DB
			if($query->sql("SELECT * FROM ".UT_LANGUAGES." WHERE id = '".$value."'")) {
				session()->value("language", $value);
			}
			// $value is not valid country
			else {
				session()->value("language", defined("DEFAULT_LANGUAGE_ISO") ? DEFAULT_LANGUAGE_ISO : "EN");
			}
		}
		// get
		else {
			// language has not been set for current user session yet
			if(!session()->value("language")) {
				// set default language
				$this->language("");
			}

			// return current user language
			return session()->value("language");
		}
	}

	/**
	* Get array of available languages
	* Optional get details for specific language
	*
	* @return Array of languages or array of language details
	*/
	function languages($id = false) {

		if(!cache()->value("languages")) {

			$query = new Query();
			$query->sql("SELECT * FROM ".UT_LANGUAGES);
			cache()->value("languages", $query->results());
		}

		// looking for specific language details
		if($id !== false) {
			$languages = cache()->value("languages");
			$key = arrayKeyValue($languages, "id", $id);
			if($key !== false) {
				return $languages[$key];
			}
			// invalid language requested - return default language
			else {
				$key = arrayKeyValue($languages, "id", $this->language());
				return $languages[$key];
			}
		}
		// return complete array of languages
		else {
			return cache()->value("languages");
		}
	}

	/**
	* Get/set current user country
	*
	* Pass value to set country
	*
	* @return country ISO id on get
	*/
	function country($value = false) {
		// set
		if($value !== false) {

			$query = new Query();
			// only allow valid country
			// look for country in DB
			if($query->sql("SELECT * FROM ".UT_COUNTRIES." WHERE id = '".$value."'")) {
				session()->value("country", $value);
			}
			// $value is not valid country
			else {
				session()->value("country", defined("DEFAULT_COUNTRY_ISO") ? DEFAULT_COUNTRY_ISO : "DK");
			}
		}

		// get
		else {

			// country has not been set for current user session yet
			if(!session()->value("country")) {
				// set default country
				$this->country("");
			}

			// return current user country
			return session()->value("country");
		}
	}

	/**
	* Get array of available countries (with details)
	* Optional get details for specific country
	*
	* @return Array of countries or array of country details
	*/
	function countries($id = false) {

		if(!cache()->value("countries")) {

			$query = new Query();
			$query->sql("SELECT * FROM ".UT_COUNTRIES);
			cache()->value("countries", $query->results());
		}

		// looking for specific country details
		if($id !== false) {
			$countries = cache()->value("countries");
			$key = arrayKeyValue($countries, "id", $id);
			if($key !== false) {
				return $countries[$key];
			}
			// invalid country requested - return default country
			else {
				$key = arrayKeyValue($countries, "id", $this->country());
				return $countries[$key];
			}
		}
		// return complete array of countries
		else {
			return cache()->value("countries");
		}

	}


	/**
	* Get/set current user currency
	*
	* Pass value to set currency
	*
	* @return currency ISO id on get
	*/
	function currency($value = false) {
		// set
		if($value !== false) {

			$query = new Query();
			// only allow valid currency
			// look for currency in DB
			if($query->sql("SELECT * FROM ".UT_CURRENCIES." WHERE id = '".$value."'")) {
				session()->value("currency", $value);
			}
			// $value is not valid currency
			else {
				session()->value("currency", defined("DEFAULT_CURRENCY_ISO") ? DEFAULT_CURRENCY_ISO : "DKK");
			}
		}

		// get
		else {

			// currency has not been set for current user session yet
			if(!session()->value("currency")) {
				// set default currency
				$this->currency("");
			}

			// return current user currency
			return session()->value("currency");
		}
	}

	/**
	* Get array of available currencies
	* Optional get details for specific currency
	*
	* @return Array of currencies or array of currency details
	*/
	function currencies($id = false) {

		if(!cache()->value("currencies")) {

			$query = new Query();
			$query->sql("SELECT * FROM ".UT_CURRENCIES);
			cache()->value("currencies", $query->results());
		}

		// looking for specific currency details
		if($id !== false) {
			$currencies = cache()->value("currencies");
			$key = arrayKeyValue($currencies, "id", $id);
			if($key !== false) {
				return $currencies[$key];
			}
			// invalid currency requested - return default currency
			else {
				$key = arrayKeyValue($currencies, "id", $this->currency());
				return $currencies[$key];
			}
		}
		// return complete array of currencies
		else {
			return cache()->value("currencies");
		}
	}


	/**
	* Get array of available vatrates
	* Optional get details for specific vatrate
	*
	* @return Array of vatrates or array of vatrate details
	*/
	function vatrates($id = false) {

		if(!cache()->value("vatrates")) {

			$query = new Query();
			$query->sql("SELECT * FROM ".UT_VATRATES);
			cache()->value("vatrates", $query->results());
		}

		// looking for specific vatrate details
		if($id !== false) {
			$vatrates = cache()->value("vatrates");
			$key = arrayKeyValue($vatrates, "id", $id);
			if($key !== false) {
				return $vatrates[$key];
			}
			// invalid vatrate requested
			else {
				return false;
			}
		}
		// return complete array of vatrates
		else {
			return cache()->value("vatrates");
		}
	}


	/**
	* Get array of available subscription methods
	* Optional get details for specific subscription method
	*
	* @return Array of subscription methods or array of subscription method details
	*/
	function subscriptionMethods($id = false) {

		if(!cache()->value("subscription_methods")) {

			$query = new Query();
			$query->sql("SELECT * FROM ".UT_SUBSCRIPTION_METHODS);
			cache()->value("subscription_methods", $query->results());
		}

		// looking for specific subscription method details
		if($id !== false) {
			$subscription_methods = cache()->value("subscription_methods");

			$key = arrayKeyValue($subscription_methods, "id", $id);
			if($key !== false) {
				return $subscription_methods[$key];
			}
			// invalid subscription method requested
			else {
				return false;
			}
		}
		// return complete array of subscription methods
		else {
			return cache()->value("subscription_methods");
		}

	}


	/**
	* Get array of available payment methods
	* Optional get details for specific payment method
	*
	* @return Array of payment methods or array of payment method details
	*/
	function paymentMethods($id = false) {

		if(!cache()->value("payment_methods")) {

			$query = new Query();
			$query->sql("SELECT * FROM ".UT_PAYMENT_METHODS." ORDER BY position");
			cache()->value("payment_methods", $query->results());
		}

		// looking for specific payment method details
		if($id !== false) {
			$payment_methods = cache()->value("payment_methods");
			$key = arrayKeyValue($payment_methods, "id", $id);
			if($key !== false) {
				return $payment_methods[$key];
			}
			// invalid payment method requested
			else {
				return false;
			}
		}
		// return complete array of payment methods
		else {
			return cache()->value("payment_methods");
		}

	}


	/**
	* Get array of available maillists
	* Optional get details for specific maillist
	*
	* @return Array of maillists or array of maillist details
	*/
	function maillists($id = false) {

		if(!cache()->value("maillists")) {

			$query = new Query();
			$query->sql("SELECT * FROM ".UT_MAILLISTS);
			cache()->value("maillists", $query->results());
		}

		// looking for specific maillist details
		if($id !== false) {
			$maillists = cache()->value("maillists");
			$key = arrayKeyValue($maillists, "id", $id);
			if($key !== false) {
				return $maillists[$key];
			}
			// invalid maillist requested
			else {
				return false;
			}
		}
		// return complete array of maillists
		else {
			return cache()->value("maillists");
		}

	}



	/**
	* Access device API and get info about current useragent
	*
	* @param Array $_options Settings for segment function
	* @return Array containing device info, or fallback 
	*/
	// returns currently used browser info to be stored in session
	function segment($_options = false) {
		// writeToFile("segment function:" . $value);

//		print "segment called";
		// get any stored value
		$segment_session = session()->value("segment");

//		print "current session is: ".$segment_session;

		$value = false;
		$type = "www";


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "type"        : $type         = $_value; break;
					case "value"       : $value        = $_value; break;
				}
			}
		}


		// setting new value
		if($value !== false) {
//			if(is_string($value) && preg_match("/^(basic|desktop|desktop_ie|desktop_light|tablet|mobile|mobile_touch|mobile_light|tv)$/", $value)) {
			if(is_string($value) && preg_match("/^[a-z0-9_]+$/", $value)) {

//				print "set segment:". $value;
				// are we setting a specific type
				// if($type !== false) {
				//
				// 	$segment_session[$type] = $value;
				// }
				// // or "original" segment value
				// else {
					// always set global segment as everything is deducted from this
					// clear out existing values and prepare for new
					unset($segment_session);
					$segment_session["segment"] = $value;
//				}

				// print "existing values after set:";
				// print_r($segment_session);

				session()->value("segment", $segment_session);

//				session()->value("segment", $value);
				return true;

			}
			// invalid segment value
			return false;
		}
		// getting value for type
		else if ($type !== false){

			// print "\nget type: " . $type;
			// print "existing values before get:";
			// print_r($segment_session);

			// is something missing
			if(!$segment_session || !isset($segment_session[$type])) {
				// writeToFile("request new segment:" . $type);

//				print "\nlookup: " . $type;

				// if we don't have our base segment yet, get it now
				if(!$segment_session) {

//					print "\nno real session segment";

					$segment_session["segment"] = @file_get_contents("http://detector-v4.dearapi.com/text?ua=".(isset($_SERVER["HTTP_USER_AGENT"]) ? urlencode($_SERVER["HTTP_USER_AGENT"]) : "")."&site=".urlencode($_SERVER["HTTP_HOST"]));

					// if the request failed, pass default segment back
					// don't update - make attempt again on next function call
					if(!$segment_session["segment"]) {

//						print "\nfailed lookup";
						return "desktop";

					}

				}
//				print "\nfind type";

				// get specified interface type settings
				@include("config/segments.core.php");
				@include("config/segments.php");
				// 

				// print "\ninclusion done: " . $segment_session["segment"];
				// print_r($segments_config);

				if(isset($segments_config[$type]) && isset($segments_config[$type][$segment_session["segment"]])) {

//					print "\nset type: " . $segments_config[$type][$segment_session["segment"]];
					$segment_session[$type] = $segments_config[$type][$segment_session["segment"]];

				}
				else if(isset($segments_config["www"]) && isset($segments_config["www"][$segment_session["segment"]])) {
//					print "\nset fallback type: " . $segments_config["www"][$segment_session["segment"]];

					$segment_session[$type] = $segments_config["www"][$segment_session["segment"]];

				}
//				else {
//					print "what the hell";
//				}

				session()->value("segment", $segment_session);

		//		$device_id = file_get_contents("http://detector.api/xml?ua=".urlencode($_SERVER["HTTP_USER_AGENT"])."&site=".urlencode($_SERVER["HTTP_HOST"]));
//				$device = (array) simplexml_load_string($device_id);
//				print_r($device);

				// if($segment) {
				// 	session()->value("segment", $segment_session);
				// }
				// else {
				// 	// offline default value
				// 	session()->value("segment", "desktop");
				// }

			}
//			print "\nreturn value: " . $segment_session[$type] . "\n";
			return $segment_session[$type];

			 //session()->value("segment");
		}

		// getting original segment value
		return isset($segment_session["segment"]) ? $segment_session["segment"] : "desktop";
	}


	/**
	* Get navigation
	*
	* Get specific navigation based on handle
	* Will be returned from cache if available
	*/
	function navigation($handle) {

		// is navigation handle specified and navigation already cached?
		if(cache()->value("navigation-".$handle)) {
			return cache()->value("navigation-".$handle);
		}

		$navigation = false;

		$query = new Query();

		// translate handle into navigation_id
		$sql = "SELECT id FROM ".UT_NAV." WHERE handle = '$handle'";
//		print $sql."<br>";
		if($query->sql($sql)) {

			$navigation_id = $query->result(0, "id");

			$navigation = false;

			$sql = "SELECT * FROM ".UT_NAV." WHERE id = '$navigation_id'";
//			print $sql."<br>";
			if($query->sql($sql)) {

				$navigation = $query->result(0);

				// get children
				$navigation["nodes"] = $this->navigationNodes($navigation_id);
			}

			// update cache
			cache()->value("navigation-".$handle, $navigation);

		}

		// return navigation
		return $navigation;

	}

	// recursive function to get navigation node tree
	function navigationNodes($navigation_id, $_options = array()) {

		// default values
		$relation = false;
		$nested_path = "";

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "relation"          : $relation           = $_value; break;
					case "nested_path"       : $nested_path        = $_value; break;
				}
			}
		}

		$query = new Query();
		$IC = new Items();


		$nodes = false;

		// with or without relations (0 when getting 1st level navigation)
		if(!$relation) {
			$sql = "SELECT * FROM ".UT_NAV_NODES." WHERE navigation_id = $navigation_id AND relation = 0 ORDER BY position ASC, id ASC";
		}
		else {
			$sql = "SELECT * FROM ".UT_NAV_NODES." WHERE navigation_id = $navigation_id AND relation = $relation ORDER BY position ASC, id ASC";
		}
//		print $sql."<br>";

		// get media
		if($query->sql($sql)) {

			$results = $query->results();
			foreach($results as $i => $node) {
				$nodes[$i]["id"] = $node["id"];
				$nodes[$i]["name"] = $node["node_name"];

				$nodes[$i]["target"] = $node["node_target"];
				$nodes[$i]["classname"] = $node["node_classname"];
				$nodes[$i]["fallback"] = $node["node_fallback"];

				// $nodes[$i]["item_id"] = $node["node_item_id"];
				// $nodes[$i]["controller"] = $node["node_item_controller"];

				// get create link for page
				if($node["node_item_id"]) {
					$page = $IC->getItem(array("id" => $node["node_item_id"]));

					// create nested link structure
					$nodes[$i]["link"] = $node["node_item_controller"].$nested_path."/".$page["sindex"];
				}
				// absolute static link
				else {
					$nodes[$i]["link"] = $node["node_link"];
				}

				// go deeper
				$_options["relation"] = $node["id"];

				// update nested paths
				$_options["nested_path"] = $nested_path."/".superNormalize($node["node_name"]);

				// get child nodes
				$nodes[$i]["nodes"] = $this->navigationNodes($navigation_id, $_options);
			}
		}

		return $nodes;
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


				$dev = session()->value("dev");
				$segment = session()->value("segment");
				$user_group_id = session()->value("user_group_id");

				session()->reset();

				# We might be here because the Group has no Access assigned. That is an Admin error.
				# user group GUEST is not to gice this error message.
				if ($user_group_id > 1) {
					$query = new Query();
					$sql = "SELECT user_group FROM ".SITE_DB.".user_groups WHERE id = ".$user_group_id." 
					AND (
						SELECT count('a') FROM ".SITE_DB.".user_access WHERE user_group_id = user_groups.id
					) = 0";

					if($query->sql($sql)) {
						$results = $query->results();
						$user_group_name = $results[0]['user_group'];
						message()->addMessage("User Group <strong>$user_group_name</strong> has no Access allocated. Please contact the Administrator.", array("type" => "error"));
					} 
				}


				

				// save current url, to be able to redirect after login
				session()->value("login_forward", $this->url);
				session()->value("dev", $dev);
				session()->value("segment", $segment);


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
			$action = substr($path, strlen($controller));

			// This will replace multiple occurences of $controller string (should only ever replace first)
			// $action = str_replace($controller, "", $path);

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


		global $mysqli_global;
		// print "controller:" . $controller . "<br>\n";
		// print "action:" . $action . "<br>\n";
		// print_r($access_item);
		// print "<br>\n";


		// all actions are allowed on SITE_INSTALL
		if((defined("SITE_INSTALL") && SITE_INSTALL)) {
//			print "all good";
			return true;
		}


		// no access restrictions
		if($access_item === false) {
			return true;
		}

		// SITE_DB is required to look up access permissions
		else if(!defined("SITE_DB") || !$mysqli_global) {
			print "Your site is not configured yet!";
			exit();
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
			//$permissions = session()->value("user_group_permissions");

//			print "group: ".$user_group_id."<br>\n";

			// TEMP
			$permissions = false;

			// any access restriction requires a user to be logged in (optionally as Guest - user_group 1, user 1)
			// no need to do any validation if no user_group_id is found
			if(!$user_group_id) {
//				print "no group<br>\n";

				return false;
			}

			$permissions = cache()->value("user_group_".$user_group_id."_permissions");


			// if permissions does not exist for this user_group in cache
			// this requires a database lookup - result is stored in cache 
			// get user_access for user_group
			if(!$permissions) {

				$query = new Query();
				$sql = "SELECT controller, action, permission FROM ".SITE_DB.".user_access WHERE user_group_id = ".$user_group_id;
				// print $sql."<br>\n";

				if($query->sql($sql)) {
					$results = $query->results();

					// parse result in easy queryable structure
					// $permission[controller][action] = 1
					foreach($results as $result) {
						$permissions[$result["controller"]][$result["action"]] = $result["permission"];
					}

				}

				cache()->value("user_group_".$user_group_id."_permissions", $permissions);
				// store permissions in session
				session()->value("user_group_permissions", $permissions);
			}


			// print_r($permissions);
			// print $controller . " /// " . $action_test . "<br>\n\n";

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
		return "";
	}


	// validate csrf token
	function validateCsrfToken() {

		// validate csrf-token on all requests? - Csrf token should always be validated (I think)
//		if(!(defined("SITE_INSTALL") && SITE_INSTALL)) {

			// if POST, check csrf token
			if($_SERVER["REQUEST_METHOD"] == "POST" &&
				(
					!isset($_POST["csrf-token"]) || 
					!$_POST["csrf-token"] || 
					$_POST["csrf-token"] != session()->value("csrf")
				)
			) {

				message()->addMessage("CSRF Autorization failed.", array("type" => "error"));

				// make sure the user is logged out (throwoff will exit)
				if(session()->value("user_id") > 1) {
					$this->throwOff();
					
				}
				// user wasn't logged in, it's probably a timeout issue
				else if($_SERVER["HTTP_REFERER"]) {
					message()->addMessage("Your session may have expired or it has been confused by multiple simultaneaous logins. Please try again.", array("type" => "error"));
					header("Location:". $_SERVER["HTTP_REFERER"]);
					exit();
				}

				return false;
			}
			else if($_SERVER["REQUEST_METHOD"] != "POST") {

				return false;

			}
//		}

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
		$password = getPostPassword("password");

		if($username && $password) {
			$query = new Query();

			// password table check
			// password table has not been upgraded
			if(!$query->sql("SELECT DISTINCT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE column_name = 'upgrade_password' AND TABLE_NAME = 'user_passwords' AND TABLE_SCHEMA = '".SITE_DB."'")) {

				include_once("classes/system/upgrade.class.php");
				$UG = new Upgrade();

				// move password to password_upgrade
				$UG->renameColumn(SITE_DB.".user_passwords", "password", "upgrade_password");
				
				// add new password column
				$UG->addColumn(SITE_DB.".user_passwords", "password", "varchar(255) NOT NULL DEFAULT ''", "user_id");

			}


			// Get user password
			$sql = "SELECT passwords.password as password, passwords.upgrade_password as upgrade_password, passwords.id as password_id FROM ".SITE_DB.".user_usernames as usernames, ".SITE_DB.".user_passwords as passwords WHERE usernames.user_id = passwords.user_id AND (passwords.password != '' OR passwords.upgrade_password != '') AND usernames.username='$username'";
			//			print "$sql<br>\n";
			if($query->sql($sql)) {
				
				$hashed_password = $query->result(0, "password");
				$sha1_password = $query->result(0, "upgrade_password");
				$password_id = $query->result(0, "password_id");
				
				
				// old sha1 password exists and matches
				// User password should be upgraded
				if($sha1_password && sha1($password) === $sha1_password) {
					
					// create new hash 
					$hashed_password = password_hash($password, PASSWORD_DEFAULT);
					if($hashed_password) {
						// and add it to password table and delete old sha1 password
						$sql = "UPDATE ".SITE_DB.".user_passwords SET upgrade_password = '', password = '$hashed_password' WHERE id = $password_id";
						$query->sql($sql);
					}
					
				}
				
				
				// hashed password corresponds to posted password
				if($hashed_password && password_verify($password, $hashed_password)) {

					// make login query
					// look for active user with verified username and password
					$sql = "SELECT users.id as id, users.user_group_id as user_group_id, users.nickname as nickname FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames, ".SITE_DB.".user_passwords as passwords WHERE users.status = 1 AND usernames.verified = 1 AND users.id = usernames.user_id AND usernames.user_id = passwords.user_id AND passwords.id = $password_id AND usernames.username='$username'";
					// print $sql;
					if($query->sql($sql)) {

						// add user_id and user_group_id to session
						session()->value("user_id", intval($query->result(0, "id")));
						session()->value("user_group_id", intval($query->result(0, "user_group_id")));
						session()->value("user_nickname", $query->result(0, "nickname"));
						session()->value("last_login_at", date("Y-m-d H:i:s"));
						session()->reset("user_group_permissions");

						// Update login timestamp
						$sql = "UPDATE ".SITE_DB.".users SET last_login_at=CURRENT_TIMESTAMP WHERE users.id = ".session()->value("user_id");
						$query->sql($sql);

						$this->addLog("Login: ".$username .", user_id:".session()->value("user_id"));

						// set new csrf token for user
						session()->value("csrf", gen_uuid());


						// regerate Session id
						session_regenerate_id(true);


						// does this class have loggedIn callback
						if(method_exists($this, "loggedIn")) {
							$user_id = session()->value("user_id");
							$this->loggedIn($user_id);
						}
						

						// Special return for ajax logins
						if(getPost("ajaxlogin")) {
							$output = new Output();
							$output->screen(array("csrf-token" => session()->value("csrf")));

						}
						else {

							// redirect to originally requested page
							$login_forward = stringOr(getVar("login_forward"), session()->value("login_forward"));
							// print "login_forward:" . $login_forward."<br>";


							// TODO: Regex is temp quickfix to avoid being redirected to API endpoints after login
							if(!$login_forward || !$this->validatePath($login_forward) || preg_match("/\/(save|update|add|remove|delete|upload|duplicate|keepAlive)/", $login_forward)) {
								$login_forward = "/";
							}

							session()->reset("login_forward");

							header("Location: " . $login_forward);
						}
						exit();
					}

					// User could not be logged in

					// is the reason, that the user has not been verified yet?
					// make login query and
					// look for user with status 0, verified = 0, password exists
					$sql = "SELECT users.id, users.nickname, usernames.username, usernames.type, usernames.verification_code FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames, ".SITE_DB.".user_passwords as passwords WHERE users.id = usernames.user_id AND usernames.user_id = passwords.user_id AND passwords.id = $password_id AND username='$username' AND verified = 0";
					// print $sql;
					if($query->sql($sql)) {

						// Make sure we have the email username
						$login_user = $query->result(0);
						if($login_user["type"] != "email") {

							// Look for user email
							$sql = "SELECT users.id, users.nickname, usernames.username, usernames.type, usernames.verification_code FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames WHERE users.id = usernames.user_id AND usernames.type='email' AND users.id = ".$login_user["id"];
		//					print "$sql<br />\n";
							if($query->sql($sql)) {
								$login_user = $query->result(0);
							}
					
						}

						// Did we find user email
						if($login_user["type"] == "email") {

							$user_id = $query->result(0, "id");
							$nickname = $query->result(0, "nickname");
							$email = $query->result(0, "username");
							$verification_code = $query->result(0, "verification_code");

							// send verification reminder email
							mailer()->send(array(
								"values" => array(
									"NICKNAME" => $nickname, 
									"EMAIL" => $email, 
									"VERIFICATION" => $verification_code,
								), 
								"recipients" => $email, 
								"template" => "signup_reminder"
							));

							$username_id = $this->getUsernameId($email, $user_id);

							// Add to user log
							$sql = "INSERT INTO ".SITE_DB.".user_log_verification_links SET user_id = ".$user_id.", username_id = ".$username_id;
				//			print $sql;
							$query->sql($sql);


							message()->addMessage("User has not been verified yet – did you forget to activate your account?", array("type" => "error"));
							return ["status" => "NOT_VERIFIED", "email" => $email];

						}

					}

				}

			}
			
			// is the reason, that the user doesn't have a password yet?
			// make login query and
			// look for user without password
			$sql = "SELECT users.id, users.nickname, usernames.username, usernames.type, usernames.verification_code FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames WHERE users.id = usernames.user_id AND usernames.user_id NOT IN (SELECT user_id FROM ".SITE_DB.".user_passwords as passwords) AND usernames.username='$username'";
//					print $sql;
			if($query->sql($sql)) {
				$login_user = $query->result(0);
				
				// Make sure we have the email username
				if($login_user["type"] != "email") {
					
					// Look for user email
					$sql = "SELECT users.id, users.nickname, usernames.username, usernames.type, usernames.verification_code FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames WHERE users.id = usernames.user_id AND usernames.type='email' AND users.id = ".$login_user["id"];
					// print "$sql<br />\n";
					if($query->sql($sql)) {
						$login_user = $query->result(0);
					}
					
				}
				
				// Did we find user email
				if($login_user["type"] == "email") {
					
					$user_id = $query->result(0, "id");
					$nickname = $query->result(0, "nickname");
					$email = $query->result(0, "username");
					$verification_code = $query->result(0, "verification_code");
					
				}
				
				// has the user not been verified yet?
				// look for user with status 0, verified = 0
				$sql = "SELECT users.id, users.nickname, usernames.username, usernames.type, usernames.verification_code FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames WHERE users.status = 0 AND users.id = usernames.user_id AND username='$username' AND verified = 0";

				if($query->sql($sql)) {
					// send verification reminder email
					mailer()->send(array(
						"values" => array(
							"NICKNAME" => $nickname, 
							"EMAIL" => $email, 
							"VERIFICATION" => $verification_code,
						), 
						"recipients" => $email, 
						"template" => "signup_reminder"
					));				
					
					$username_id = $this->getUsernameId($email, $user_id);

					
					// Add to user log
					$sql = "INSERT INTO ".SITE_DB.".user_log_verification_links SET user_id = ".$user_id.", username_id = ".$username_id;
		//			print $sql;
					$query->sql($sql);

					return ["status" => "NOT_VERIFIED", "email" => $email];

				}

				// has the user been verified and subsequently deactivated?
				// look for user with status 0, verified = 1
				$sql = "SELECT users.id, users.nickname, usernames.username, usernames.type, usernames.verification_code FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames WHERE users.status = 0 AND users.id = usernames.user_id AND username='$username' AND verified = 1";

				if($query->sql($sql)) {
					$this->addLog("Login error: ".$username);

					message()->addMessage("Computer says NO!", array("type" => "error"));
					return false;

				}

				$username_id = $this->getUsernameId($email, $user_id);
				
				// Add to user log
				$sql = "INSERT INTO ".SITE_DB.".user_log_verification_links SET user_id = ".$user_id.", username_id = ".$username_id;
	//			print $sql;
				$query->sql($sql);
				return ["status" => "NO_PASSWORD", "email" => $email];

			}

		}

		$this->addLog("Login error: ".$username);

		message()->addMessage("Computer says NO!", array("type" => "error"));
		return false;
	}

	/**
	* Log in using token
	*/
	function tokenLogIn() {

		// Allow GET parameters
		$token = getVar("token");
		$username = getVar("username");

		if($token && $username) {
			$query = new Query();

			// make login query
			// look for user with username and password
			$sql = "SELECT users.id as id, users.user_group_id as user_group_id, users.nickname as nickname FROM ".SITE_DB.".users as users, ".SITE_DB.".user_apitokens as tokens, ".SITE_DB.".user_usernames as usernames WHERE users.status = 1 AND users.id = usernames.user_id AND usernames.user_id = tokens.user_id AND tokens.token='$token' AND usernames.username='$username'";
//			print $sql;
			if($query->sql($sql)) {


				// add user_id and user_group_id to session
				session()->value("user_id", $query->result(0, "id"));
				session()->value("user_group_id", $query->result(0, "user_group_id"));
				session()->reset("user_group_permissions");
				session()->value("user_nickname", $query->result(0, "nickname"));

				$this->addLog("Token login: ".$username ." (".session()->value("user_id").")");

				// set new csrf token for user
				session()->value("csrf", gen_uuid());

				// regerate Session id
				session_regenerate_id(true);

				if(getVar("credentials")) {
					$output = new Output();
					$output->screen(array("csrf-token" => session()->value("csrf")));
					exit;
				}

				return;
			}
		}

		$this->addLog("Token login error: ".$username);

		message()->addMessage("Computer says NO!", array("type" => "error"));
		return false;
	}

	/**
	* Simple logoff
	* Logoff user and redirect to login page
	*/
	function logOff() {

		$this->addLog("Logoff: user_id:".session()->value("user_id"));
		//$this->user_id = "";

		session()->reset("user_id");
		session()->reset("user_group_id");
		session()->reset("user_group_permissions");

		$dev = session()->value("dev");
		$segment = session()->value("segment");


		// Delete cart reference cookie
		setcookie("cart_reference", "", time() - 3600, "/");
		
		// Reset session (includes destroy, start and regenerate)
		session()->reset();

		// Remember dev and segment even after logout
		session()->value("dev", $dev);
		session()->value("segment", $segment);

		header("Location: /");
		exit();
	}

	/**
	* Throw off if user is caught on page without permission
	*
	* @param String $url Optional url to forward to after login
	*/
	function throwOff($url=false) {

		$url = $url ? $url : $this->url;

		// Log and send in email
		$this->addLog("Throwoff - insufficient privileges:".$url." by ". session()->value("user_id"));
		mailer()->send(array(
			"subject" => "Throwoff - " . SITE_URL, 
			"message" => "insufficient privileges:".$url, 
			"template" => "system"
		));

		// something is fishy, clean up
		unset($_GET);
		unset($_POST);
		unset($_FILES);

		// Preserve messages
		$messages = $_SESSION["message"];

		//$this->user_id = "";
		session()->reset();

		// Restore messages
		$_SESSION["message"] = $messages;

		session()->value("login_forward", $url);
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

				echo "Failed to connect to DB: " . $mysqli->connect_error;
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

		// Only load DB constants if connect_db was loaded successfully
		if(defined("SITE_DB")) {
			@include_once("config/database.constants.php");
		}

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

			// include formatting template (if it exists)
			@include("templates/mails/notifications/$collection.php");

			// send and reset collection
			if(mailer()->send(array(
				"subject" => "NOTIFICATION: $collection on ".$_SERVER["SERVER_ADDR"], 
				"message" => $message,
				"tracking" => false
			))) {
				$fp = fopen($collection_file, "w");
				fclose($fp);
			}
		}
	}

}

?>
