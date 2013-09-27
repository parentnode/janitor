<?php
/**
* This file contains validation for building a model functionality
*/
class Model extends HTML {

	public $data_entities;
	public $data_errors;

	/**
	* Construct reference to data object
	*/
	function __construct() {

		// TODO: get base elements from Item (published_at, status, etc.?)

		$this->getPostedEntities();
		
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

		$id = false;

		$value = "";
		$type = "string";
		$label = false;
		$required = false;
		$hint_message = "Must be " . $type;
		$error_message = "*";

		// string lengt, file count, number value
		$min = false;
		$max = false;

		// files
		$allowed_formats = "gif,jpg,png,mp4,mov,m4v,pdf";
		$allowed_proportions = "*";
		$allowed_sizes = "*";

		$unique = false;
		$pattern = false;

		// dates
		$is_before = false;
		$is_after = false;

		// passwords
		$must_match = false;

		// currency
		$currency = false;
		$vatrate = false;

		$options = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "id"                    : $id                    = $_value; break;
					case "value"                 : $value                 = $_value; break;
					case "type"                  : $type                  = $_value; break;
					case "label"                 : $label                 = $_value; break;
					case "required"              : $required              = $_value; break;
					case "error_message"         : $error_message         = $_value; break;
					case "hint_message"          : $hint_message          = $_value; break;

					case "min"                   : $min                   = $_value; break;
					case "max"                   : $max                   = $_value; break;


					case "allowed_formats"       : $allowed_formats       = $_value; break;
					case "allowed_proportions"   : $allowed_proportions   = $_value; break;
					case "allowed_sizes"         : $allowed_sizes         = $_value; break;

					case "is_before"             : $is_before             = $_value; break;
					case "is_after"              : $is_after              = $_value; break;

					case "must_match"            : $must_match            = $_value; break;
					case "unique"                : $unique                = $_value; break;
					case "pattern"               : $pattern               = $_value; break;

					case "currency"              : $currency              = $_value; break;
					case "vatrate"               : $vatrate               = $_value; break;

					case "options"               : $options               = $_value; break;
				}
			}
		}
	
		$this->data_entities[$name]["id"] = $id;
		$this->data_entities[$name]["value"] = $value;
		$this->data_entities[$name]["type"] = $type;
		$this->data_entities[$name]["label"] = $label;

		$this->data_entities[$name]["required"] = $required;
		$this->data_entities[$name]["error_message"] = $error_message;
		$this->data_entities[$name]["hint_message"] = $hint_message;

		$this->data_entities[$name]["min"] = $min;
		$this->data_entities[$name]["max"] = $max;

		$this->data_entities[$name]["allowed_formats"] = $allowed_formats;
		$this->data_entities[$name]["allowed_proportions"] = $allowed_proportions;
		$this->data_entities[$name]["allowed_sizes"] = $allowed_sizes;

		$this->data_entities[$name]["is_before"] = $is_before;
		$this->data_entities[$name]["is_after"] = $is_after;

		$this->data_entities[$name]["must_match"] = $must_match;
		$this->data_entities[$name]["unique"] = $unique;
		$this->data_entities[$name]["pattern"] = $pattern;

		$this->data_entities[$name]["currency"] = $currency;
		$this->data_entities[$name]["vatrate"] = $vatrate;

		$this->data_entities[$name]["options"] = $options;

		// $this->setValidationIndication($element);
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
				$entity["value"] = getPost($name);
				$this->data_entities[$name]["value"] = getPost($name);
			}
		}
	}
	
	function getEntityProperty($name, $property) {
		return isset($this->data_entities[$name][$property]) ? $this->data_entities[$name][$property] : "";
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

		if(count($this->data_errors)) {
			foreach($this->data_entities as $name => $entity) {
				$this->data_entities[$name]["value"] = prepareForHTML($entity["value"]);
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

		foreach($list as $name) {
			if(isset($this->data_entities[$name])) {
				if(!$this->validate($name, $item_id)) {
					$this->data_errors[$name] = true;
				}
			}
		}
		if(count($this->data_errors)) {
			foreach($this->data_entities as $name => $entity) {
				$this->data_entities[$name]["value"] = prepareForHTML($entity["value"]);
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
	*/
	function validate($name, $item_id = false) {
//		print "validate:".$name;

		// check uniqueness
		if($this->data_entities[$name]["unique"]) {
			if(!$this->isUnique($name, $item_id)) {
				return false;
			}
		}

		// is optional and empty?
		// if value is not empty - it needs to be validated even for optional entities
		if(!$this->data_entities[$name]["required"] && $this->data_entities[$name]["value"] == "") {
			return true;
		}

		// string or text field
		if($this->data_entities[$name]["type"] == "string" || $this->data_entities[$name]["type"] == "text") {
			if($this->isString($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "files") {
			if($this->isFiles($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "images") {
			if($this->isImages($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "number") {
			if($this->isNumber($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "integer") {
			if($this->isInteger($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "email") {
			if($this->isEmail($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "tel") {
			if($this->isTelephone($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "password") {
			if($this->comparePassword($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "array") {
			if($this->isArray($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "date") {
			if($this->isDate($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "timestamp") {
			if($this->isTimestamp($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "prices") {
			if($this->isPrices($name)) {
				return true;
			}
		}
		else if($this->data_entities[$name]["type"] == "tags") {
			if($this->isTags($name)) {
				return true;
			}
		}
		
		// either type was not found or validation failed
		return false;
	}


	/**
	* Check for other existance of value
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function isUnique($name, $item_id) {
		$entity = $this->data_entities[$name];

		$query = new Query();
		// does other value exist
		if($query->sql("SELECT id FROM ".$entity["unique"]." WHERE $name = '".$entity["value"]."'".$item_id ? " AND item_id != ".$item_id : "")) {
			return false;
		}

		return true;
	}

	/**
	* Check if element has an "optional" rule and value is empty
	*
	* @param string $element Element identifier
	* @param string $value Optional value to override internal value array (in case of file upload)
	* @return bool
	*/
	// function isEmptyOptional($name) {
	// 	$entity = $this->data_entities[$name];
	// 
	// 	if($entity["required"] == false && $entity["value"] == "") {
	// 		return true;
	// 	}
	// 	return false;
	// }

	/**
	* Is file valid?
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function isFiles($name) {
		$entity = $this->data_entities[$name];
		
		// if($_FILES[$element]["name"] && $_FILES[$element]["tmp_name"] && !$_FILES[$element]["error"]) {
		return true;
		// }
		// 
		// return false;
	}

	/**
	* Is image valid?
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function isImages($name) {
		$entity = $this->data_entities[$name];

		if($_FILES[$element]["name"] && $_FILES[$element]["tmp_name"] && !$_FILES[$element]["error"]) {
			$image_info = getimagesize($_FILES[$element]["tmp_name"]);
			$image_mime = image_type_to_mime_type($image_info[2]);
			if((!$width || ($width && $image_info[0] == $width)) && (!$height || ($height && $image_info[1] == $height))) {
				return true;
			}
		}

		return false;
	}

	/**
	* Is string string?
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function isString($name) {
		$entity = $this->data_entities[$name];

		$value = $entity["value"];

		$min_length = $entity["min"];
		$max_length = $entity["max"];
		$pattern = $entity["pattern"];

		if($value && is_string($value) && 
			(!$min_length || strlen($value) >= $min_length) && 
			(!$max_length || strlen($value) <= $max_length) &&
			(!$pattern || preg_match("/^".$pattern."$/", $value))
		) {
			$this->data_entities[$name]["error"] = false;
			return true;
		}
		else {
			$this->data_entities[$name]["error"] = true;
			return false;
		}
	}

	/**
	* Is string numeric?
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function isNumber($name) {
		$entity = $this->data_entities[$name];

		$min = $this->getRuleDetails($rule, 0);
		$max = $this->getRuleDetails($rule, 1);

		if(is_numeric($string) &&  (!$min || $string >= $min) && (!$max || $string <= $max)) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	* Check if email is correctly formatted
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function isEmail($name) {
		$entity = $this->data_entities[$name];

		$value = $entity["value"];

		$pattern = stringOr($entity["pattern"], "[\w\.\-\_]+@[\w-\.]+\.\w{2,4}");

		if($value && is_string($value) && 
			(!$pattern || preg_match("/^".$pattern."$/", $value))
		) {
			$this->data_entities[$name]["error"] = false;
			return true;
		}
		else {
			$this->data_entities[$name]["error"] = true;
			return false;
		}
	}


	/**
	* Check if email is correctly formatted
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function isTelephone($name) {
		$entity = $this->data_entities[$name];

		$value = $entity["value"];

		$pattern = stringOr($entity["pattern"], "([\+0-9\-\.\s\(\)]){5,18}");

		if($value && is_string($value) && 
			(!$pattern || preg_match("/^".$pattern."$/", $value))
		) {
			$this->data_entities[$name]["error"] = false;
			return true;
		}
		else {
			$this->data_entities[$name]["error"] = true;
			return false;
		}
	}


	/**
	* Compare two passwords (to check if password and repeat password are identical)
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
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
	* Check if array is array
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function isArray($name) {
		$entity = $this->data_entities[$name];

		$array = $this->obj->vars[$element];
		$min_length = $this->getRuleDetails($rule, 0);
		if(is_array($array) && count(cleanArray($array)) && (!$min_length || count(cleanArray($array)) >= $min_length)) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	* Check if date is entered correctly
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*
	* TODO: update validation
	*/
	function isDate($name) {
		$entity = $this->data_entities[$name];


		return true;

		$after = $this->getRuleDetails($rule, 0);
		$before = $this->getRuleDetails($rule, 1);

		$this->obj->vars[$element] = preg_replace('/[\/\.-]/', '-', $this->obj->vars[$element]);
		$string = $this->obj->vars[$element];
		$date = explode('-', $string);
		if(count($date) == 3) {
			$timestamp = mktime(0,0,0,$date[1], $date[0], $date[2]);

			if(checkdate($date[1], $date[0], $date[2]) && (!$after || $timestamp > $after) && (!$before || $timestamp < $before)) {
				return true;
			}
		}
		return false;
	}

	/**
	* Check if timestamp is entered correctly
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function isTimestamp($name) {
		$entity = $this->data_entities[$name];

		$after = $this->getRuleDetails($rule, 0);
		$before = $this->getRuleDetails($rule, 1);

		list($date, $time) = explode(" ", $this->obj->vars[$element]);

		$date = preg_replace('/[\/\.-]/', '-', $date);
		$string = $this->obj->vars[$element];

		$date = explode('-', $date);
		$time = explode(':', $time);

		if(count($date) == 3 && count($time) == 2) {
			$timestamp = mktime($time[0], $time[1], 0, $date[1], $date[0], $date[2]);

			if(checkdate($date[1], $date[0], $date[2]) && (!$after || $timestamp > $after) && (!$before || $timestamp < $before)) {
				return true;
			}
		}
		return false;
	}

	function isTags($name) {
		$entity = $this->data_entities[$name];

		return true;
	}
	function isPrices($name) {
		$entity = $this->data_entities[$name];

		return true;
	}





	// TODO: UPDATE getPostVars name and functionallity
	// TODO: UPDATE type.product.class in save function

	/**
	* Prepare variables in Vars array to be returned to page (because of error or like)
	*/
	// function prepareVars() {
	// 	foreach($this->obj->vars as $element => $value) {
	// 		if(is_array($value)) {
	// 			foreach($value as $index => $val) {
	// 				$this->obj->vars[$element][$index] = stripslashes($val);
	// 			}
	// 		}
	// 		else {
	// 			$this->obj->vars[$element] = stripslashes($value);
	// 		}
	// 	}
	// }

	// /**
	// * Set validation indication for element
	// *
	// * @param string $element Element identifier
	// * @param string $indication Indication of validation
	// * @return bool
	// */
	// function setValidationIndication($element, $indication=false) {
	// 	if(!$this->checkOptional($element)) {
	// 		if(!is_array($this->obj->varnames[$element])) {
	// 			$varname = $this->obj->varnames[$element];
	// 			$this->obj->varnames[$element] = array();
	// 			$this->obj->varnames[$element]['value'] = $varname;
	// 		}
	// 		$this->obj->varnames[$element]['validation'] = $indication ? $indication : "*";
	// 	}
	// }

	// /**
	// * Set error value for element
	// *
	// * @param string $element Element identifier
	// * @param string $error Error message
	// * @return bool
	// */
	// function setError($element, $error) {
	// 	if(!is_array($this->obj->varnames[$element])) {
	// 		$varname = $this->obj->varnames[$element];
	// 		$this->obj->varnames[$element] = array();
	// 		$this->obj->varnames[$element]['value'] = $varname;
	// 	}
	// 	$this->obj->varnames[$element]['error'] = isset($this->obj->varnames[$element]['error']) && $this->obj->varnames[$element]['error'] ? $this->obj->varnames[$element]['error'].", ".$error : $error;
	// }

	// /**
	// * Remove error from element
	// *
	// * @param string $element Element identifier
	// */
	// function clearError($element) {
	// 	if(!is_array($this->obj->varnames[$element])) {
	// 		$varname = $this->obj->varnames[$element];
	// 		$this->obj->varnames[$element] = array();
	// 		$this->obj->varnames[$element]['value'] = $varname;
	// 	}
	// 	$this->obj->varnames[$element]['error'] = "";
	// }

	// /**
	// * Get rule details from rula args array
	// *
	// * @param string $rule Rule array
	// * @param int $index args index
	// * @return string|false
	// */
	// function getRuleDetails($rule, $index) {
	// 	if(isset($rule["args"]) && isset($rule["args"][$index])) {
	// 		return $rule["args"][$index];
	// 	}
	// 	else {
	// 		return false;
	// 	}
	// }

}

?>