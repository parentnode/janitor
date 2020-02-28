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


		// Default values (relevant for all models)

		// Default type
		$this->data_defaults["type"] = "string";

		// Default html setting (no media for non-items)
		$this->data_defaults["allowed_tags"] = "p,h1,h2,h3,h4,h5,h6,code,ul,ol";


		// Standard system extensions

		$this->addToModel("user_id", array(
			"type" => "user_id",
			"label" => "User",
			"hint_message" => "Please select a user", 
			"error_message" => "User must exist"
		));
		$this->addToModel("item_id", array(
			"type" => "item_id",
			"label" => "Item",
			"hint_message" => "Please select an item",
			"error_message" => "Item must exist"
		));

	}


	/**
	* All entity to current model
	* See documentation for full options overview
	*/
	function addToModel($name, $_options = false) {
		// debug(["addToModel: $name", $_options]);

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
					case "autocomplete"          : $this->setProperty($name, "autocomplete",         $_value); break;
					case "unique"                : $this->setProperty($name, "unique",               $_value); break;
					case "pattern"               : $this->setProperty($name, "pattern",              $_value); break;

					case "compare_to"            : $this->setProperty($name, "compare_to",           $_value); break;


					case "min"                   : $this->setProperty($name, "min",                  $_value); break;
					case "max"                   : $this->setProperty($name, "max",                  $_value); break;


					case "step"                  : $this->setProperty($name, "step",                 $_value); break;


					case "min_width"             : $this->setProperty($name, "min_width",            $_value); break;
					case "min_height"            : $this->setProperty($name, "min_height",           $_value); break;


					case "allowed_formats"       : $this->setProperty($name, "allowed_formats",      $_value); break;
					case "allowed_proportions"   : $this->setProperty($name, "allowed_proportions",  $_value); break;
					case "allowed_sizes"         : $this->setProperty($name, "allowed_sizes",        $_value); break;


					case "allowed_tags"          : $this->setProperty($name, "allowed_tags",         $_value); break;
					case "media_add"             : $this->setProperty($name, "media_add",            $_value); break;
					case "media_delete"          : $this->setProperty($name, "media_delete",         $_value); break;
					case "file_add"              : $this->setProperty($name, "file_add",             $_value); break;
					case "file_delete"           : $this->setProperty($name, "file_delete",          $_value); break;


					case "searchable"            : $this->setProperty($name, "searchable",           $_value); break;


					case "error_message"         : $this->setProperty($name, "error_message",        $_value); break;
					case "hint_message"          : $this->setProperty($name, "hint_message",         $_value); break;

				}
			}
		}

	}


	/**
	* Get model for current itemtype
	*/
	function getModel() {
		return $this->data_entities;
	}


	/**
	* Set property value
	*
	* @param string $name Name of property
	* @param string $property Property to set value of
	* @param string|array $value Value to be assigned
	*/
	function setProperty($name, $property, $value) {
		$this->data_entities[$name][$property] = $value;
	}

	/**
	* Get value of property from model entity
	* Fall back to default value or false
	*
	* @param string $name Name of property
	* @param string $property Property to get value of
	* @return string Value of $property on $name or default property from the data_defaults array – or false if none of the above
	*/
	function getProperty($name, $property) {
		if(isset($this->data_entities[$name][$property])) {
			return $this->data_entities[$name][$property];
		}

		return isset($this->data_defaults[$property]) ? $this->data_defaults[$property] : false;
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
						$this->setProperty($name, "value", $_FILES[$name]["tmp_name"]);
					}
					else {
						$this->setProperty($name, "value", false);
					}

				}
				// Special case for passwords
				else if($this->getProperty($name, "type") == "password") {

					// Don't sanitize posted passwords
					$value = getPostPassword($name);
					$this->setProperty($name, "value", $value);

				}
				// regular variable
				else {

					$value = getPost($name);
					$this->setProperty($name, "value", $value);

				}

			}
		}

	}


	/**
	* Execute validation rule (rules defined in data object)
	*
	* @param String $Element Element to validate
	* @param Integer $item_id Optional item_id to check against (in case of uniqueness)
	* @return bool
	*/
	function validate($name, $item_id = false) {
		// debug(["validate:".$name, $this->getProperty($name, "type"), $this->getProperty($name, "value")]);

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
		// NOTO: Also work with unchecked checkboxes, where value is 0 (MAK 2019-01-25 – check for unforseen consequences)
		// if(!$this->getProperty($name, "required") && $this->getProperty($name, "value") == "") {

		// compare_to value is relavant even on empty fields
		$compare_to = $this->getProperty($name, "compare_to");

		// Pre validation (is full validation needed)
		if(!$this->getProperty($name, "required") && 
			(
				$this->getProperty($name, "value") === "" 
				|| 
				$this->getProperty($name, "value") === false 
				||
				// Special case for checkbox
				(
					$this->getProperty($name, "type") == "checkbox"
					&& 
					$this->getProperty($name, "value") === "0"
				)
			)
			&&
			(
				!$compare_to || $this->getProperty($name, "value") == $this->getProperty($compare_to, "value")
			)
		) {
			return true;
		}


		// string or text field
		if(
			$this->getProperty($name, "type") == "hidden" || 
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
			if($this->isPassword($name)) {
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
		else if($this->getProperty($name, "type") == "checkbox") {
			if($this->isChecked($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "radiobuttons") {
			if($this->isChecked($name)) {
				return true;
			}
		}

		else if($this->getProperty($name, "type") == "range") {
			if($this->isRange($name)) {
				return true;
			}
		}

		else if($this->getProperty($name, "type") == "location") {
			// No composite validation for location (should look at location, longitude and latitude)
			if($this->isString($name)) {
				return true;
			}
		}
		// else if($this->getProperty($name, "type") == "prices") {
		// 	if($this->isPrices($name)) {
		// 		return true;
		// 	}
		// }

		else if($this->getProperty($name, "type") == "tag") {
			if($this->isTag($name)) {
				return true;
			}
		}

		else if($this->getProperty($name, "type") == "user_id") {
			if($this->isUser($name)) {
				return true;
			}
		}
		else if($this->getProperty($name, "type") == "item_id") {
			if($this->isItem($name)) {
				return true;
			}
		}

		// either type was not found or validation failed
		$error_message = $this->getProperty($name, "error_message");
		$error_message = $error_message && $error_message != "*" ? $error_message : "An unknown validation error occurred";
		message()->addMessage($error_message, array("type" => "error"));
		return false;
	}



	/**
	* Execute defined validation rules for all elements (rules defined in data object)
	*
	* @param string Optional elements to skip can be passed as parameters
	* @return bool
	*/
	function validateAll($execpt = false, $item_id = false) {
		$this->data_errors = array();

		// debug([$this->data_entities]);
		if(count($this->data_entities)) {

			foreach($this->data_entities as $name => $entity) {

				if(!$execpt || array_search($name, $execpt) === false) {
					// debug(["validating name: $name"]);

					if(!$this->validate($name, $item_id)) {

						// debug(["error: $name"]);
						$this->data_errors[$name] = true;
					}
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
	* Execute defined validation rules for listed elements (rules defined in data object)
	*
	* @param string Element names to validate
	* @return bool
	*/
	function validateList($list, $item_id = false) {
		$this->data_errors = array();

		// debug([$this->data_entities]);
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
	* Check for other existence of value
	*
	* @param string $name Entity name
	* @param Integer $item_id current item_id
	* @return bool
	*/
	function isUnique($name, $item_id) {

		$value = $this->getProperty($name, "value");
		$db_table = $this->getProperty($name, "unique");

		$query = new Query();
		$sql = "SELECT id FROM ".$db_table." WHERE $name = '".$value."'".($item_id ? " AND item_id != ".$item_id : "");

		// does other value exist
		if($query->sql($sql)) {
			$this->setProperty($name, "error", true);
			return false;
		}

		$this->setProperty($name, "error", false);
		return true;
	}

	/**
	* Check if user_id is valid user
	*
	* @param string $name Entity name
	* @return bool
	*/
	function isUser($name) {

		$value = $this->getProperty($name, "value");

		$UC = new User();
		if(!$UC->getUserInfo(array("user_id" => $value))) {
			$this->setProperty($name, "error", true);
			return false;
		}

		$this->setProperty($name, "error", false);
		return true;
	}

	/**
	* Check if item_id is valid Item
	*
	* @param string $name Entity name
	* @return bool
	*/
	function isItem($name) {

		$value = $this->getProperty($name, "value");

		$IC = new Items();
		if(!$IC->getItem(array("id" => $value))) {
			$this->setProperty($name, "error", true);
			return false;
		}

		$this->setProperty($name, "error", false);
		return true;
	}

	/**
	* Is file valid?
	*
	* @param string $name Entity name
	* @return bool
	*/
	function isFiles($name) {

		$value = $this->getProperty($name, "value");

		// file count
		$min = $this->getProperty($name, "min");
		$max = $this->getProperty($name, "max");

		// file formats
		$formats = $this->getProperty($name, "allowed_formats");

		// Image/Video minimum size requirements
		$min_width = $this->getProperty($name, "min_width");
		$min_height = $this->getProperty($name, "min_height");

		$proportions = $this->getProperty($name, "allowed_proportions");
		$sizes = $this->getProperty($name, "allowed_sizes");

		// Get upload information
		$uploads = $this->identifyUploads($name);

		if(
			$uploads &&
			(!$min || count($uploads) >= $min) && 
			// Max defaults to one file on file inputs
			((!$max && count($uploads) <= 1)|| count($uploads) <= $max) &&

			(!$min_width || $this->filesMinWidthTest($uploads, $min_width)) &&
			(!$min_height || $this->filesMinHeightTest($uploads, $min_height)) &&

			(!$proportions || $proportions === "*" || $this->filesProportionTest($uploads, $proportions)) &&
			(!$sizes || $sizes === "*" || $this->fileSizeTest($uploads, $sizes)) &&
			(!$formats || $formats === "*" || $this->fileFormatTest($uploads, $formats))
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
	// test minimum width
	function filesMinWidthTest($uploads, $min_width) {

		foreach($uploads as $upload) {
			// if uploaded width is not sufficient
			if(!isset($upload["width"]) || $upload["width"] < $min_width) {
				return false;
			}
		}
		return true;
	}

	// isFiles helper
	// test minimum height
	function filesMinHeightTest($uploads, $min_height) {

		foreach($uploads as $upload) {
			// if uploaded height is not sufficient
			if(!isset($upload["height"]) || $upload["height"] < $min_height) {
				return false;
			}
		}
		return true;
	}

	// isFiles helper
	// test if proportions are valid
	function filesProportionTest($uploads, $proportions) {

		$proportion_array = explode(",", $proportions);
		foreach($proportion_array as $i => $proportion) {
			$proportion_array[$i] = $proportion;
		}
		foreach($uploads as $upload) {
			// if uploaded proportion is not allowed
			if(!isset($upload["proportion"]) || array_search($upload["proportion"], $proportion_array) === false) {
				return false;
			}
		}
		return true;
	}

	// isFiles helper
	// test if sizes are valid
	function fileSizeTest($uploads, $sizes) {

		$size_array = explode(",", $sizes);
		foreach($uploads as $upload) {
			// if uploaded size is not allowed
			if(!isset($upload["width"]) || !isset($upload["height"]) || array_search($upload["width"]."x".$upload["height"], $size_array) === false) {
				return false;
			}
		}
		return true;
	}

	// isFiles helper
	// test if formats are valid
	function fileFormatTest($uploads, $formats) {

		$format_array = explode(",", $formats);
		foreach($uploads as $upload) {
			// if uploaded format is not allowed
			if(array_search($upload["format"], $format_array) === false) {
				return false;
			}
		}
		return true;
	}

	// isFiles helper
	// upload identification helper
	// supports identification in these groups:
	// - image
	// - video
	// - audio
	// - other
	function identifyUploads($name) {
		// debug(["identifyUploads: $name", $_FILES]);

		$uploads = array();

		if(isset($_FILES[$name])) {

			foreach($_FILES[$name]["name"] as $index => $value) {
				if(!$_FILES[$name]["error"][$index] && file_exists($_FILES[$name]["tmp_name"][$index])) {

					$file = $_FILES[$name]["tmp_name"][$index];

					$upload = array();
					$upload["name"] = $value;
					$upload["type"] = $_FILES[$name]["type"][$index];
					$upload["format"] = mimetypeToExtension($upload["type"]);
					$upload["filesize"] = filesize($file);
					$upload["file"] = $file;

					// Width and height will be set below for uploads where it exists (movie/images)
					$upload["width"] = "";
					$upload["height"] = "";


					// video upload (mp4/mov)
					// extract more properties
					if(preg_match("/video\//", $upload["type"])) {

						include_once("classes/helpers/video.class.php");
						$Video = new Video();

						// check if we can get relevant info about movie
						$info = $Video->info($file);
						if($info) {

							// TODO: add better bitrate detection to Video Class
							// TODO: add duration
							// $upload["bitrate"] = $info["bitrate"];

							$upload["width"] = $info["width"];
							$upload["height"] = $info["height"];
							$upload["proportion"] = round($upload["width"] / $upload["height"], 4);

							$uploads[] = $upload;
						}

					}

					// audio upload (mp3/wav/aac)
					// extract more properties
					else if(preg_match("/audio\//", $upload["type"])) {

						include_once("classes/helpers/audio.class.php");
						$Audio = new Audio();

 						// check if we can get relevant info about audio
						$info = $Audio->info($file);
						if($info) {

							// TODO: add bitrate detection
							// TODO: add duration
							// $upload["bitrate"] = $info["bitrate"];

							$uploads[] = $upload;
						}

					}

					// image upload (gif/png/jpg)
					// extract more properties
					else if(preg_match("/image\//", $upload["type"])) {

						// Create Imagick object from file
						$image = new Imagick($file);

 						// check if we can get relevant info about image
						$info = $image->getImageFormat();
						if($info) {

							// TODO: add dpi detection

							$upload["width"] = $image->getImageWidth();
							$upload["height"] = $image->getImageHeight();
							$upload["proportion"] = round($upload["width"] / $upload["height"], 4);

							$uploads[] = $upload;
						}

					}

					// ALL OTHER UPLOADS (pdf/zip/etc)
					else {

						// No additional properties to add

						$uploads[] = $upload;

					}

				}

			}

		}

		return $uploads;
	}

	/**
	* Is input range?
	*
	* @param string $name Entity name
	* @return bool
	*/
	function isRange($name) {

		$value = $this->getProperty($name, "value");
		$min = $this->getProperty($name, "min");
		$max = $this->getProperty($name, "max");

		if(($value || $value === "0") && is_numeric($value) && 
			(!$min || $value >= $min) && 
			(!$max || $value <= $max)
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
	* @param string $name Entity name
	* @return bool
	*/
	function isString($name) {

		$value = $this->getProperty($name, "value");
		$min_length = $this->getProperty($name, "min");
		$max_length = $this->getProperty($name, "max");
		$pattern = $this->getProperty($name, "pattern");
		$compare_to = $this->getProperty($name, "compare_to");

		if(($value || $value === "0") && is_string($value) && 
			(!$min_length || strlen($value) >= $min_length) && 
			(!$max_length || strlen($value) <= $max_length) &&
			(!$pattern || preg_match("/^".$pattern."$/", $value)) &&
			(!$compare_to || $value == $this->getProperty($compare_to, "value"))
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
	* @param string $name Entity name
	* @return bool
	*/
	function isHTML($name) {

		$value = $this->getProperty($name, "value");
		$min_length = $this->getProperty($name, "min");
		$max_length = $this->getProperty($name, "max");

		// remove all HTML tags
		$stripped_value = strip_tags($value);

		if(
			$stripped_value != $value &&
			($stripped_value || $stripped_value === "0") && is_string($stripped_value) && 
			(!$min_length || strlen($stripped_value) >= $min_length) && 
			(!$max_length || strlen($stripped_value) <= $max_length)
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
	* @param string $name Entity name
	* @return bool
	*/
	function isNumber($name) {

		$value = $this->getProperty($name, "value");
		$min = $this->getProperty($name, "min");
		$max = $this->getProperty($name, "max");
		$pattern = $this->getProperty($name, "pattern");

		if(($value || $value === "0") && is_numeric($value) && 
			(!$min || $value >= $min) && 
			(!$max || $value <= $max) &&
			(!$pattern || preg_match("/^".$pattern."$/", $value))
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
	* @param string $name Entity name
	* @return bool
	*/
	function isInteger($name) {

		$value = $this->getProperty($name, "value");
		$min = $this->getProperty($name, "min");
		$max = $this->getProperty($name, "max");
		$pattern = $this->getProperty($name, "pattern");

		if(($value || $value === "0") && is_numeric($value) && !fmod($value, 1) && 
			(!$min || $value >= $min) && 
			(!$max || $value <= $max) &&
			(!$pattern || preg_match("/^".$pattern."$/", $value))
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
	* @param string $name Entity name
	* @return bool
	*/
	function isEmail($name) {

		$value = $this->getProperty($name, "value");
		$pattern = stringOr($this->getProperty($name, "pattern"), "^[\w\.\-\_\+]+@[\w-\.]+\.\w{2,10}$");
		$compare_to = $this->getProperty($name, "compare_to");

		if($value && is_string($value) && 
			(!$pattern || preg_match("/^".$pattern."$/", $value)) &&
			(!$compare_to || $value == $this->getProperty($compare_to, "value"))
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
	* @param string $name Entity name
	* @return bool
	*/
	function isTelephone($name) {

		$value = $this->getProperty($name, "value");
		$pattern = stringOr($this->getProperty($name, "pattern"), "([\+0-9\-\.\s\(\)]){5,18}");
		$compare_to = $this->getProperty($name, "compare_to");

		if($value && is_string($value) && 
			(!$pattern || preg_match("/^".$pattern."$/", $value)) &&
			(!$compare_to || $value == $this->getProperty($compare_to, "value"))
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
	* Is password valid?
	*
	* @param string $name ent identifier
	* @return bool
	*/
	function isPassword($name) {

		$value = $this->getProperty($name, "value");
		$min_length = $this->getProperty($name, "min");
		$max_length = $this->getProperty($name, "max");
		$pattern = $this->getProperty($name, "pattern");
		$compare_to = $this->getProperty($name, "compare_to");

		if(($value || $value === "0") && is_string($value) && 
			(!$min_length || strlen($value) >= $min_length) && 
			(!$max_length || strlen($value) <= $max_length) &&
			(!$pattern || preg_match("/^".$pattern."$/", $value)) &&
			(!$compare_to || $value == $this->getProperty($compare_to, "value"))
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
	* @param string $name Entity name
	* @return bool
	*/
	function isTag($name) {

		$value = $this->getProperty($name, "value");
		$pattern = stringOr($this->getProperty($name, "pattern"), "[a-z]+:.+");

		if($value && 
			(is_numeric($value) || (!$pattern || preg_match("/^".$pattern."$/", $value)))
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
	* @param string $name Entity name
	* @return bool
	*/
	function isDatetime($name) {

		$value = $this->getProperty($name, "value");
		$pattern = stringOr($this->getProperty($name, "pattern"), "[\d]{4}-[\d]{2}-[\d]{2} [0-9]{1,2}[:-]{1}[0-9]{2}[0-9:-]*");
		$after = $this->getProperty($name, "min");
		$before = $this->getProperty($name, "max");

		if($value && 
			(!$before || strtotime(toTimestamp($value)) <= strtotime(toTimestamp($before))) && 
			(!$after || strtotime(toTimestamp($value)) >= strtotime(toTimestamp($after))) &&

			(!$pattern || preg_match("/^".$pattern."$/", $value))
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
	* @param string $name Entity name
	* @return bool
	*/
	function isDate($name) {

		$value = $this->getProperty($name, "value");
		$pattern = stringOr($this->getProperty($name, "pattern"), "[\d]{4}-[\d]{2}-[\d]{2}[0-9\-\/ \:]*");
		$after = $this->getProperty($name, "min");
		$before = $this->getProperty($name, "max");

		if($value && 
			(!$before || strtotime(toTimestamp($value)) <= strtotime(toTimestamp($before))) && 
			(!$after || strtotime(toTimestamp($value)) >= strtotime(toTimestamp($after))) &&

			(!$pattern || preg_match("/^".$pattern."$/", $value))
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
	* Check if checkbox/radiobutton is checked
	*
	* @param string $name Entity name
	* @return bool
	*/
	function isChecked($name) {

		$value = $this->getProperty($name, "value");

		// "0" not accepted as valid answer (flowover from checkbox where "0" means not checked)
		if($value) {
			$this->setProperty($name, "error", false);
			return true;
		}
		else {
			$this->setProperty($name, "error", true);
			return false;
		}
	}




	// // TODO: Faulty price validation
	// function isPrices($name) {
	// 	$entity = $this->data_entities[$name];
	//
	// 	return true;
	// }


}

?>