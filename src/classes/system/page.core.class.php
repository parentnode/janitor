<?php


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
	protected $actions;
	protected $permissions;


	// page output variables
	public $page_title;
	public $page_description;
	public $page_image;
	public $page_type;

	public $body_class;
	public $content_class;
	public $header_includes;



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
			session()->value("ip", security()->getRequestIp());
			session()->value("useragent", ((isset($_SERVER["HTTP_USER_AGENT"]) && $_SERVER["HTTP_USER_AGENT"]) ? stripslashes($_SERVER["HTTP_USER_AGENT"]) : "Unknown"));
			session()->value("last_login_at", date("Y-m-d H:i:s"));
		}

//		print session()->value("user_id").", ".session()->value("user_group_id")."<br>";

		// shorthand for clean request uri
		$this->url = str_replace("?".$_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']);


		// login in progress
		if(getVar("login") == "true") {
			security()->logIn();
			// $this->logIn();
		}
		// login in progress
		if(getVar("token") && getVar("username")) {
			security()->tokenLogIn();
		}
		// logoff
		if(getVar("logoff") == "true") {
			security()->logOff();
		}

		// set segment
		if(getVar("segment")) {
			// set real segment value
			$this->segment(["value" => getVar("segment"), "type" => "segment"]);
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

			$og_type = "article";

			if($_options !== false) {
				foreach($_options as $_option => $_value) {
					switch($_option) {
						case "description"       : $description_index    = $_value; break;
						case "title"             : $title_index          = $_value; break;
						case "image"             : $image_index          = $_value; break;

						case "type"              : $og_type              = $_value; break;
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
							$this->pageImage("/images/".$item["id"]."/".$image["variant"]."/1200x627.".$image["format"]);
						}
						// Standard size for everyone else
						else {
							$this->pageImage("/images/".$item["id"]."/".$image["variant"]."/250x.jpg");
						}

						break;
					}
				}
			}

			// Set type
			$this->pageType($og_type);

		}
		else {

			$_ = '';

			$_ .= '<meta property="og:title" content="'.$this->pageTitle().'" />';
			$_ .= '<meta property="og:description" content="'.$this->pageDescription().'" />';
			$_ .= '<meta property="og:image" content="'.SITE_URL.$this->pageImage().'" />';
			$_ .= '<meta property="og:url" content="'.SITE_URL.$this->url.'" />';
			$_ .= '<meta property="og:type" content="'.$this->pageType().'" />';

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
		// set iamge
		if($value !== false) {
			$this->page_image = $value;
		}
		// get image
		else {
			// if image already set
			if($this->page_image) {
				return $this->page_image;
			}
			// Default page image from config file if available
			else if(defined("DEFAULT_PAGE_IMAGE")) {
				return DEFAULT_PAGE_IMAGE;
			}

			// last resort favicon
			return "/favicon.png";
		}
	}

	/**
	* Get/set page type
	*
	* - fallback to website
	*
	* @return String page type
	*/
	function pageType($value = false) {

		// set title
		if($value !== false) {
			$this->page_type = $value;
		}
		// get title
		else {
			// if title already set
			if($this->page_type) {
				return $this->page_type;
			}

			// last resort - use constant
			return "website";
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
					if(preg_match("/\.js($|\?)/", $include)) {
						$_ .= '<script type="text/javascript" src="'.$include.'"></script>';
					}
					else if(preg_match("/\.css($|\?)/", $include)) {
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
	* Get array of available price_types
	* Optional get details for specific price_type
	*
	* @return Array of price_types or array of price_type details
	*/
	function price_types($_options = false) {

		$IC = new Items();

		$id = false;
		$exclude_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "id"                   : $id                    = $_value; break;
					case "exclude_id"           : $exclude_id            = $_value; break;
				}
			}
		}

		if(!cache()->value("price_types")) {

			$query = new Query();
			$query->sql("SELECT * FROM ".UT_PRICE_TYPES);
			cache()->value("price_types", $query->results());
		}

		// looking for specific price_type details
		if($id !== false) {
			$price_types = cache()->value("price_types");
			$key = arrayKeyValue($price_types, "id", $id);
			if($key !== false) {
				return $price_types[$key];
			}
			// invalid price_type requested
			else {
				return false;
			}
		}
		// exclude price_type of a specific item_id 
		else if($exclude_id !== false) {
			$IC = new Items();

			$price_types = cache()->value("price_types");

			$key = arrayKeyValue($price_types, "item_id", $exclude_id);

			if($key) {
				unset($price_types[$key]);
			}
		}
		// return complete array of price_types
		else {
			$price_types = cache()->value("price_types");
		}

		// exclude price_type for disabled items
		foreach($price_types as $key => $price_type) {

			if(isset($price_type["item_id"])) {
				$item = $IC->getItem(["id" => $price_type["item_id"]]);
				
				if($item && $item["status"] === "0") {
					unset($price_types[$key]);
				}
			}
		}

		return $price_types;
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

		// get any stored value
		$segment_session = session()->value("segment");

		// debug(["current session is:", $segment_session, "options", $_options]);

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

			if(is_string($value) && preg_match("/^[a-z0-9_]+$/", $value)) {

				// always set global segment as everything is deducted from this
				// clear out existing values and prepare for new
				unset($segment_session);
				$segment_session["segment"] = $value;

				session()->value("segment", $segment_session);

				return true;

			}
			// invalid segment value
			return false;
		}
		// getting value for type
		else if ($type !== false){

			// debug(["getting current segment:", $segment_session, "options", $_options]);

			// is something missing
			if(!$segment_session || !isset($segment_session[$type])) {

				// if we don't have our base segment yet, get it now
				if(!$segment_session) {

					$segment_session = [];
					$segment_session["segment"] = @file_get_contents("https://detector.dearapi.com/text?ua=".(isset($_SERVER["HTTP_USER_AGENT"]) ? urlencode($_SERVER["HTTP_USER_AGENT"]) : "")."&site=".urlencode($_SERVER["HTTP_HOST"]));

					// if the request failed, pass default segment back
					// don't update - make attempt again on next function call
					if(!$segment_session["segment"]) {

						return "desktop";

					}

				}


				// get specified interface type settings
				@include("config/segments.core.php");
				@include("config/segments.php");


				if(isset($segments_config[$type]) && isset($segments_config[$type][$segment_session["segment"]])) {

					$segment_session[$type] = $segments_config[$type][$segment_session["segment"]];

				}
				else if(isset($segments_config["www"]) && isset($segments_config["www"][$segment_session["segment"]])) {

					$segment_session[$type] = $segments_config["www"][$segment_session["segment"]];

				}

				session()->value("segment", $segment_session);

			}

			// Perhaps invalid segment was specified – no type found
			if(isset($segment_session[$type])) {
				return $segment_session[$type];
			}

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


		$nodes = [];

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
	* This function is automatically called when controller is accessed and $page is instantiated
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
			if(!security()->checkPermissions($controller, $action, $access_item)) {


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
				header("Location: ".SITE_LOGIN_URL);
				exit();

			}
		}

		// access_item is false in controller
		// SITE_INSTALL
		// OR validation passed
		// -  access is allowed
		$this->actions = $actions;

	}


	// Get Page actions
	function actions() {
		return $this->actions;
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

		$db_host = isset($settings["host"]) ? $settings["host"] : "";
		$db_username = isset($settings["username"]) ? $settings["username"] : "";
		$db_password = isset($settings["password"]) ? $settings["password"] : "";

		$mysqli = false;

		// Attempt to make connection
		try {
			$mysqli = new mysqli($db_host, $db_username, $db_password);
		}
		catch (mysqli_sql_exception $e) {
			error_log("Main MySql connect: " . $e->__toString());
		}


		if(!$mysqli || $mysqli->connect_errno) {

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

}

?>
