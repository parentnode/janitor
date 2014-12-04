<?php
/**
* This file contains validation for building a model functionality
*/
class Model extends HTML {

	public $data_defaults;
	public $data_entities;
	public $data_errors;


	/**
	* Construct reference to data object
	*/
	function __construct() {

		// current controller path
		$this->path = preg_replace("/\.php$/", "", $_SERVER["SCRIPT_NAME"]);


		// Default values

		$this->data_defaults["type"] = "string";

		// files
		$this->data_defaults["allowed_formats"] = "gif,jpg,png,mp4,mov,m4v,pdf";

		// html
		$this->data_defaults["allowed_tags"] = "p,h1,h2,h3,h4,h5,h6,code,ul,download";


		global $page;

		// TODO: maybe these standard settings should be in Core Itemtype
		// define default models (Janitor model allows these element on all itemtypes)
		// optimized for backend implementation

		$this->addToModel("published_at", array(
			"type" => "datetime",
			"label" => "Publish date (yyyy-mm-dd hh:mm)",
			"hint_message" => "Publishing date of the item. Leave empty for current time", 
			"error_message" => "Datetime must be of format (yyyy-mm-dd hh:mm)"
		));

		$this->addToModel("tags", array(
			"type" => "tag",
			"label" => "Tag",
			"hint_message" => "Select existing tag or add a new tag.",
			"error_message" => "Tag must conform to tag format: context:value."
		));

		$this->addToModel("html", array(
			"type" => "html",
			"label" => "HTML",
			"allowed_tags" => "p,h2,h3,h4,ul,ol,download", //,mp4,png,jpg,vimeo,youtube,code",
			"hint_message" => "Write!",
			"error_message" => "No words? How weird.",
			"file_delete" => $page->validPath($this->path."/deleteHTMLFile"),
			"file_add" => $page->validPath($this->path."/addHTMLFile")
		));

		$this->addToModel("mediae", array(
			"type" => "files",
			"label" => "Add media here",
			"allowed_formats" => "png,jpg,mp4",
			"hint_message" => "Add images or videos here. Use png, jpg or mp4.",
			"error_message" => "Media does not fit requirements."
		));

		$this->addToModel("single_media", array(
			"type" => "files",
			"label" => "Add media here",
			"max" => 1,
			"allowed_formats" => "png,jpg,mp4",
			"hint_message" => "Add images or videos here. Use png, jpg or mp4 in 960x540.",
			"error_message" => "Media does not fit requirements."
		));



	}


	/**
	* Validation types
	* optional => validation will be ignored if value is empty
	*
	* text => var has to contain text (or number)
	* optional extra arguments:
	* 1: minimum length
	* 2: maximum length
	*
	* num => var has to be a number
	* optional extra arguments:
	* 1: minimum value
	* 2: maximum value
	*
	* file => checking $_FILES[$element]["name"] and $_FILES[$element]["error"]
	* no extra arguments:
	*
	* image => checking $_FILES[$element]["name"] and $_FILES[$element]["error"]
	* optional extra arguments:
	* 1: width
	* 2: height
	*
	* email => var has to be valid formatted email
	* optional extra arguments:
	* 1: database to check for other appearances of value
	* 2: separate existance error message
	*
	* pwr => (password repeat) var has to be equal to pw
	* required extra arguments:
	* 1: password
	*
	* arr => var has to be an array
	* optional extra arguments:
	* 1: minimum length
	*
	* unik => var has to be unik value
	* required extra arguments:
	* 1: database to check for other appearances of value
	* 2: database field to check for other appearances of value (optional, default = element name)
	*
	* date => var has to be valid date DD[.-/]MM[.-/][YY]YY
	* optional extra arguments:
	* 1: after timestamp
	* 2: before timestamp
	*
	* timestamp => var has to be valid timestamp DD[.-/]MM[.-/][YY]YY hh:mm
	* optional extra arguments:
	* 1: after timestamp
	* 2: before timestamp
	*/
	function addToModel($name, $_options = false) {

		// print "addToModel:".$name."<br>\n";
		// print_r($_options);


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "label"                 : $this->setProperty($name, "label",                $_value); break;
					case "type"                  : $this->setProperty($name, "type",                 $_value); break;
					case "value"                 : $this->setProperty($name, "value",                $_value); break;
					case "options"               : $this->setProperty($name, "options",              $_value); break;

					case "id"                    : $this->setProperty($name, "id",                   $_value); break;
					case "class"                 : $this->setProperty($name, "class",                $_value); break;

					case "required"              : $this->setProperty($name, "required",             $_value); break;
					case "unique"                : $this->setProperty($name, "unique",               $_value); break;
					case "pattern"               : $this->setProperty($name, "pattern",              $_value); break;


					case "min"                   : $this->setProperty($name, "min",                  $_value); break;
					case "max"                   : $this->setProperty($name, "max",                  $_value); break;

					case "allowed_formats"       : $this->setProperty($name, "allowed_formats",      $_value); break;
					case "allowed_proportions"   : $this->setProperty($name, "allowed_proportions",  $_value); break;
					case "allowed_sizes"         : $this->setProperty($name, "allowed_sizes",        $_value); break;

					case "allowed_tags"          : $this->setProperty($name, "allowed_tags",         $_value); break;

					case "is_before"             : $this->setProperty($name, "is_before",            $_value); break;
					case "is_after"              : $this->setProperty($name, "is_after",             $_value); break;

					case "must_match"            : $this->setProperty($name, "must_match",           $_value); break;

					case "error_message"         : $this->setProperty($name, "error_message",        $_value); break;
					case "hint_message"          : $this->setProperty($name, "hint_message",         $_value); break;

					case "file_add"              : $this->setProperty($name, "file_add",             $_value); break;
					case "file_delete"           : $this->setProperty($name, "file_delete",          $_value); break;

				}
			}
		}

	}


	function getModel() {
		return $this->data_entities;
	}

	function getModelNames() {
		$names = false;
		foreach($this->data_entities as $name) {
			$names[] = $name;
		}
		return $names;
	}

	/**
	* Getting all vars defined through the varnames array
	* Inserts the values of variables defined in vars-array
	*
	* @param array $varnames Array of variable names
	* @return array Vars array
	* @uses getVar
	*/
	function getPostedEntities() {

		if(count($this->data_entities)) {
			foreach($this->data_entities as $name => $entity) {

				// special case with files
				if($this->getProperty($name, "type") == "files") {

					// indicate value is present for file upload
					if(isset($_FILES[$name])) {
//						$this->data_entities[$name]["value"] = true;
//						$this->data_entities[$name]["value"] = $_FILES[$name]["tmp_name"];
						$this->setProperty($name, "value", $_FILES[$name]["tmp_name"]);
					}
					else {
						$this->setProperty($name, "value", false);
//						$_FILES[$name]["tmp_name"]
					}
				}

				// regular variable
				else {
					$value = getPost($name);
//					if($value !== false) {
//						print $name."=".$value."\n";
						$this->setProperty($name, "value", $value);
//						$this->data_entities[$name]["value"] = $value;
//					}
					// else {
					// 	print "should be false:" . $name . "," . ($this->data_entities[$name]["value"] === false) . "\n";
					// }
				}
			}
		}
	}


	/**
	* Set property value
	*
	* TODO: Documentation required
	*/
	function setProperty($name, $property, $value) {
		$this->data_entities[$name][$property] = $value;
	}

	/**
	* Get property from model
	* Fall back to default value or false
	*
	* TODO: Documentation required
	*/
	function getProperty($name, $property) {
		if(isset($this->data_entities[$name][$property])) {
			return $this->data_entities[$name][$property];
		}

		return isset($this->data_defaults[$property]) ? $this->data_defaults[$property] : false;
	}

	/**
	* Execute defined validation rules for all elements (rules defined in data object)
	*
	* @param string Optional elements to skip can be passed as parameters
	* @return bool
	*/
	function validateAll($execpt = false, $item_id = false) {
		$this->data_errors = array();

//		print "<p>";
//		print_r($this->data_entities);
		if(count($this->data_entities)) {

			foreach($this->data_entities as $name => $entity) {

				if(!$execpt || array_search($name, $execpt) === false) {
//					print "validationg name: $name<br>";

					if(!$this->validate($name, $item_id)) {
//						print "error:<br>";
						$this->data_errors[$name] = true;
					}
				}
			}
		}
//		print "</p>";

		// prepare values to be returned to screen if errors exist
		if(count($this->data_errors)) {
			foreach($this->data_entities as $name => $entity) {
				if($this->getProperty($name, "value") !== false) {
					$this->setProperty($name, "value", prepareForHTML($this->getProperty($name, "value")));
				}
			}
			return false;
		}
		else {
			return true;
		}
	}

	/**
	* Execute defined validation rules for listed elements (rules defined in data object)
	*
	* @param string Elements to validate
	* @return bool
	*/
	function validateList($list = false, $item_id = false) {
		$this->data_errors = array();

//		print_r($this->data_entities);
		foreach($list as $name) {
			if(isset($this->data_entities[$name])) {
				if(!$this->validate($name, $item_id)) {
					$this->data_errors[$name] = true;
				}
			}
		}

		// prepare values to be returned to screen if errors exist
		if(count($this->data_errors)) {
			foreach($this->data_entities as $name => $entity) {
				if($this->getProperty($name, "value") !== false) {
					$this->setProperty($name, "value", prepareForHTML($this->getProperty($name, "value")));
				}
			}
			return false;
		}
		else {
			return true;
		}
	}

	/**
	* Execute validation rule (rules defined in data object)
	*
	* @param String $Element Element to validate
	* @param Integer $item_id Optional item_id to check aganist (in case of uniqueness)
	* @return bool
	*
	* TODO: some validation rules are not done!
	*/
	function validate($name, $item_id = false) {
//		print "validate:".$name.", ".$this->getProperty($name, "type").", ".$this->getProperty($name, "value")."\n";

		// check uniqueness
		if($this->getProperty($name, "unique")) {
			if(!$this->isUnique($name, $item_id)) {
				$error_message = $this->getProperty($name, "error_message");
				$error_message = $error_message && $error_message != "*" ? $error_message : "An unknown validation error occured (uniqueness)";
				message()->addMessage($error_message, array("type" => "error"));
				return false;
			}
		}

		// is optional and empty?
		// if value is not empty - it needs to be validated even for optional entities
		if(!$this->getProperty($name, "required") && $this->getProperty($name, "value") == "") {
			return true;
		}

		// string or text field
		if(
			$this->getProperty($name, "type") == "string" || 
			$this->getProperty($name, "type") == "text"  || 
			$this->getProperty($name, "type") == "select"
		) {
			if($this->isString($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "html") {
			if($this->isHTML($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "files") {
			if($this->isFiles($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "number") {
			if($this->isNumber($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "integer") {
			if($this->isInteger($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "email") {
			if($this->isEmail($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "tel") {
			if($this->isTelephone($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "password") {
			if($this->getProperty($name, "compare_to")) {
				if($this->comparePassword($name, $this->getProperty($name, "compare_to"))) {
					return true;
				}
			}
			else if($this->isString($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "date") {
			if($this->isDate($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "datetime") {
			if($this->isDatetime($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "prices") {
			if($this->isPrices($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "tag") {
			if($this->isTag($name)) {
				return true;
			}
		}

		// either type was not found or validation failed
		$error_message = $this->getProperty($name, "error_message");
		$error_message = $error_message && $error_message != "*" ? $error_message : "An unknown validation error occured";
		message()->addMessage($error_message, array("type" => "error"));
		return false;
	}


	/**
	* Check for other existance of value
	*
	* @param string $name Element identifier
	* @param Integer $item_id current item_id
	* @return bool
	*/
	function isUnique($name, $item_id) {

		$value = $this->getProperty($name, "value");
		$db = $this->getProperty($name, "unique");

		$query = new Query();
		$sql = "SELECT id FROM ".$db." WHERE $name = '".$value."'".($item_id ? " AND item_id != ".$item_id : "");
		if($item_id) {
			
		}
		// does other value exist
		if($query->sql($sql)) {
			$this->setProperty($name, "error", true);
			return false;
		}

		$this->setProperty($name, "error", false);
		return true;
	}

	/**
	* Is file valid?
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isFiles($name) {
		// print "isFiles:<br>";
		// print "FILES:\n";
		// print_r($_FILES);

		$value = $this->getProperty($name, "value");
		$min = $this->getProperty($name, "min");
		$max = $this->getProperty($name, "max");

		$formats = $this->getProperty($name, "allowed_formats");
		$proportions = $this->getProperty($name, "allowed_proportions");
		$sizes = $this->getProperty($name, "allowed_sizes");

		$uploads = $this->identifyUploads($name);


		// print "sizes:".$sizes."\n";
		// print "uploads:\n";
		// print_r($uploads);

		if(
			(!$min || count($value) >= $min) && 
			(!$max || count($value) <= $max) &&
			(!$proportions || $this->proportionTest($uploads, $proportions)) &&
			(!$sizes || $this->sizeTest($uploads, $sizes)) &&
			(!$formats || $this->formatTest($uploads, $formats))
		) {
			$this->setProperty($name, "error", false);
			return true;
		}
		else {
			$this->setProperty($name, "error", true);
			return false;
		}

	}

	// isFiles helper
	// test if proportions are valid
	function proportionTest($uploads, $proportions) {

		$proportion_array = explode(",", $proportions);
		foreach($proportion_array as $i => $proportion) {
			$proportion_array[$i] = round($proportion, 2);
		}
		foreach($uploads as $upload) {
			if(!isset($upload["proportion"]) || array_search($upload["proportion"], $proportion_array) === false) {
//				print "bad proportion";
				return false;
			}
		}
		return true;
	}

	// isFiles helper
	// test if sizes are valid
	function sizeTest($uploads, $sizes) {

		$size_array = explode(",", $sizes);
		foreach($uploads as $upload) {
			if(!isset($upload["width"]) || !isset($upload["height"]) || array_search($upload["width"]."x".$upload["height"], $size_array) === false) {
//				print "bad size";
				return false;
			}
		}
		return true;
	}

	// isFiles helper
	// test if formats are valid
	function formatTest($uploads, $formats) {

		$format_array = explode(",", $formats);
		foreach($uploads as $upload) {
			if(array_search($upload["format"], $format_array) === false) {
//				print "bad format:".$upload["format"]."<br>\n";
				return false;
			}
		}
		return true;
	}

	// isFiles helper
	// upload identification helper
	// supports identification of:
	// - image
	// - video
	// - audio
	function identifyUploads($name) {

		$uploads = array();

		// print "input_name:" . $name;
		// print_r($_FILES);

		if(isset($_FILES[$name])) {
//			print_r($_FILES[$name]);

//			if($_FILES[$name]["name"])
			foreach($_FILES[$name]["name"] as $index => $value) {
				if(!$_FILES[$name]["error"][$index] && file_exists($_FILES[$name]["tmp_name"][$index])) {

					$upload = array();
					$upload["name"] = $value;

					$temp_file = $_FILES[$name]["tmp_name"][$index];
					$temp_type = $_FILES[$name]["type"][$index];
					$temp_extension = mimetypeToExtension($temp_type);


					// video upload (mp4)
					if(preg_match("/video/", $temp_type)) {

						include_once("classes/system/video.class.php");
						$Video = new Video();

						// check if we can get relevant info about movie
						$info = $Video->info($temp_file);
						if($info) {

							// TODO: add extension to Video Class
							// TODO: add better bitrate detection to Video Class
							// TODO: add duration
							// $upload["bitrate"] = $info["bitrate"];
							$upload["type"] = "movie";
							$upload["filesize"] = filesize($temp_file);
							$upload["format"] = $temp_extension;
							$upload["width"] = $info["width"];
							$upload["height"] = $info["height"];
							$upload["proportion"] = round($upload["width"] / $upload["height"], 2);
							$uploads[] = $upload;
						}

					}

					// audio upload (mp3)
					else if(preg_match("/audio/", $temp_type)) {

						include_once("classes/system/audio.class.php");
						$Audio = new Audio();

 						// check if we can get relevant info about audio
						$info = $Audio->info($temp_file);
						if($info) {
//							print_r($info);

							// TODO: add bitrate detection
							// TODO: add duration
							// $upload["bitrate"] = $info["bitrate"];
							$upload["type"] = "audio";
							$upload["filesize"] = filesize($temp_file);
							$upload["format"] = $temp_extension;
							$uploads[] = $upload;
						}

					}

					// image upload (gif/png/jpg)
					else if(preg_match("/image/", $temp_type)) {

						$image = new Imagick($temp_file);

 						// check if we can get relevant info about image
						$info = $image->getImageFormat();
						if($info) {

							$upload["type"] = "image";
							$upload["filesize"] = filesize($temp_file);
							$upload["format"] = $temp_extension;
							$upload["width"] = $image->getImageWidth();
							$upload["height"] = $image->getImageHeight();
							$upload["proportion"] = round($upload["width"] / $upload["height"], 2);
							$uploads[] = $upload;

						}
					}

					// application upload (pdf/zip)
					else if(preg_match("/application/", $temp_type)) {

						// PDF
						if($temp_extension == "pdf") {

							$upload["type"] = "pdf";
							$upload["filesize"] = filesize($temp_file);
							$upload["format"] = $temp_extension;
							$uploads[] = $upload;

						}
						// ZIP
						else if($temp_extension == "zip") {

							$upload["type"] = "zip";
							$upload["filesize"] = filesize($temp_file);
							$upload["format"] = $temp_extension;
							$uploads[] = $upload;

						}
					}
				}
			}

		}

		return $uploads;

	}



	/**
	* Is string string?
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isString($name) {

		$value = $this->getProperty($name, "value");
		$min_length = $this->getProperty($name, "min");
		$max_length = $this->getProperty($name, "max");
		$pattern = $this->getProperty($name, "pattern");

		if(($value || $value === "0") && is_string($value) && 
			(!$min_length || strlen($value) >= $min_length) && 
			(!$max_length || strlen($value) <= $max_length) &&
			(!$pattern || preg_match("/".$pattern."/", $value))
		) {
			$this->setProperty($name, "error", false);
			return true;
		}
		else {
			$this->setProperty($name, "error", true);
			return false;
		}
	}


	/**
	* Is string string?
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isHTML($name) {

		$value = $this->getProperty($name, "value");
		$min_length = $this->getProperty($name, "min");
		$max_length = $this->getProperty($name, "max");

		// remove all HTML tags
		$value = strip_tags($value);

		if(
			($value || $value === "0") && is_string($value) && 
			(!$min_length || strlen($value) >= $min_length) && 
			(!$max_length || strlen($value) <= $max_length)
		) {
			$this->setProperty($name, "error", false);
			return true;
		}
		else {
			$this->setProperty($name, "error", true);
			return false;
		}
	}


	/**
	* Is string numeric?
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isNumber($name) {

		$value = $this->getProperty($name, "value");
		$min = $this->getProperty($name, "min");
		$max = $this->getProperty($name, "max");
		$pattern = $this->getProperty($name, "pattern");

		if(($value || $value === "0") && !($value%1) && 
			(!$min || $value >= $min) && 
			(!$max || $value <= $max) &&
			(!$pattern || preg_match("/".$pattern."/", $value))
		) {
			$this->setProperty($name, "error", false);
			return true;
		}
		else {
			$this->setProperty($name, "error", true);
			return false;
		}
	}

	/**
	* Is string integer?
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isInteger($name) {

		$value = $this->getProperty($name, "value");
		$min = $this->getProperty($name, "min");
		$max = $this->getProperty($name, "max");
		$pattern = $this->getProperty($name, "pattern");

//		print ($value || $value === "0") . ", " . (!($value%1)) . ", " . (!$min || $value >= $min) . ", ". (!$max || $value <= $max) . ", " . (!$pattern || preg_match("/".$pattern."/", $value)) . ";";

		if(($value || $value === "0") && !($value%1) && 
			(!$min || $value >= $min) && 
			(!$max || $value <= $max) &&
			(!$pattern || preg_match("/".$pattern."/", $value))
		) {
			$this->setProperty($name, "error", false);
			return true;
		}
		else {
			$this->setProperty($name, "error", true);
			return false;
		}
	}

	/**
	* Check if email is correctly formatted
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isEmail($name) {

		$value = $this->getProperty($name, "value");
		$pattern = stringOr($this->getProperty($name, "pattern"), "^[\w\.\-\_]+@[\w-\.]+\.\w{2,4}$");

		if($value && is_string($value) && 
			(!$pattern || preg_match("/".$pattern."/", $value))
		) {
			$this->setProperty($name, "error", false);
			return true;
		}
		else {
			$this->setProperty($name, "error", true);
			return false;
		}
	}


	/**
	* Check if phonenumber is correctly formatted
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isTelephone($name) {

		$value = $this->getProperty($name, "value");
		$pattern = stringOr($this->getProperty($name, "pattern"), "^([\+0-9\-\.\s\(\)]){5,18}$");

		if($value && is_string($value) && 
			(!$pattern || preg_match("/".$pattern."/", $value))
		) {
			$this->setProperty($name, "error", false);
			return true;
		}
		else {
			$this->setProperty($name, "error", true);
			return false;
		}
	}

	/**
	* Check if tag is correctly formatted
	* Tag can be tag id or new tag string
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isTag($name) {

		$value = $this->getProperty($name, "value");
		$pattern = stringOr($this->getProperty($name, "pattern"), "^[a-z]+:.+$");

		if($value && 
			(is_numeric($value) || (!$pattern || preg_match("/".$pattern."/", $value)))
		) {
			$this->setProperty($name, "error", false);
			return true;
		}
		else {
			$this->setProperty($name, "error", true);
			return false;
		}
	}

	/**
	* Check if datetime is entered correctly
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isDatetime($name) {

		$value = $this->getProperty($name, "value");
		$pattern = stringOr($this->getProperty($name, "pattern"), "^[\d]{4}-[\d]{2}-[\d]{2} [0-9]{1,2}[:-]{1}[0-9]{2}[0-9:-]*$");
		$is_before = $this->getProperty($name, "is_before");
		$is_after = $this->getProperty($name, "is_after");

		if($value && 
			(!$is_before || strtotime(toTimestamp($value)) < strtotime(toTimestamp($is_before))) && 
			(!$is_after || strtotime(toTimestamp($value)) > strtotime(toTimestamp($is_before))) &&
			(!$pattern || preg_match("/".$pattern."/", $value))
		) {
			$this->setProperty($name, "error", false);
			return true;
		}
		else {
			$this->setProperty($name, "error", true);
			return false;
		}
	}

	/**
	* Check if date is entered correctly
	*
	* @param string $name Element identifier
	* @return bool
	*/
	function isDate($name) {

		$value = $this->getProperty($name, "value");
		$pattern = stringOr($this->getProperty($name, "pattern"), "^[\d]{4}-[\d]{2}-[\d]{2}[0-9\-\/ \:]*$");
		$is_before = $this->getProperty($name, "is_before");
		$is_after = $this->getProperty($name, "is_after");

		if($value && 
			(!$is_before || strtotime(toTimestamp($value)) < strtotime(toTimestamp($is_before))) && 
			(!$is_after || strtotime(toTimestamp($value)) > strtotime(toTimestamp($is_before))) &&
			(!$pattern || preg_match("/".$pattern."/", $value))
		) {
			$this->setProperty($name, "error", false);
			return true;
		}
		else {
			$this->setProperty($name, "error", true);
			return false;
		}
	}







	// NOT UPDATED VALIDATION


	/**
	* Compare two passwords (to check if password and repeat password are identical)
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	* TODO: Faulty password validation
	*/
	function comparePassword($name) {

		$entity = $this->data_entities[$name];

		$repeated_password = $this->obj->vars[$element];
		$password = $this->obj->vars[$this->getRuleDetails($rule, 0)];
		if($repeated_password == $password) {
			return true;
		}
		else {
			return false;
		}
	}



	/**
	* Check if GeoLocation is entered correctly
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*
	* TODO: faulty geolocation validation - maybe it should be deleted
	*/
	function isGeoLocation($name) {
		$entity = $this->data_entities[$name];


		return true;

	}



	// TODO: Faulty price validation
	function isPrices($name) {
		$entity = $this->data_entities[$name];

		return true;
	}

}

?>