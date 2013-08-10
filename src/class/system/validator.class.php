<?php
/**
* This file contains validation functionality
*/
class Validator {

	private $elements;
	private $obj = null;

	/**
	* Construct reference to data object
	*/
	function __construct($obj) {
		$this->obj = $obj;
	}

	/**
	* Validation types
	* optional => validation will be ignored if value is empty
	*
	* txt => var has to contain text (or number)
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
	function rule($element, $validation, $error=false) {
		$rule["type"] = $validation;
		$rule["error"] = $error ? $error : "*";

		$args = func_get_args();
		for($i = 3; $i < count($args); $i++) {
			$rule["args"][] = $args[$i];
		}
		$this->elements[$element][] = $rule;
		$this->setValidationIndication($element);
	}

	/**
	* Execute defined validation rules for all elements (rules defined in data object)
	*
	* @param string Optional elements to skip can be passed as parameters
	* @return bool
	*/
	function validateAll() {
		$errors = 0;
		$skip = func_get_args();
		if(count($this->elements)) {
			foreach($this->elements as $element => $rules) {
				$this->clearError($element);
				if(array_search($element, $skip) === false) {
					foreach($this->elements[$element] as $rule) {
						if(!$this->validate($element, $rule)) {
							$errors++;
						}
					}
				}
			}
		}
		if($errors) {
			$this->prepareVars();
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
	function validateList() {
		$errors = 0;
		$list = func_get_args();
		foreach($list as $element) {
			if(isset($this->elements[$element])) {
				foreach($this->elements[$element] as $rule) {
					if(!$this->validate($element, $rule)) {
						$errors++;
					}
				}
			}
		}
		if($errors) {
			$this->prepareVars();
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
	* @param Array $rule Validation rule information
	* @return bool
	*/
	function validate($element, $rule) {
		// optional (indirect rule)
		if($rule["type"] == "optional") {
	 		return true;
		}
		else if($rule["type"] == "txt") {
			if($this->checkString($element, $rule) || $this->checkOptional($element)) {
				return true;
			}
			else {
				$this->setError($element, $rule["error"]);
				return false;
			}
		}
		else if($rule["type"] == "file") {
			if($this->checkFile($element, $rule) || $this->checkOptional($element, $_FILES[$element]["name"])) {
				return true;
			}
			else {
				$this->setError($element, $rule["error"]);
				return false;
			}
		}
		else if($rule["type"] == "image") {
			if($this->checkImage($element, $rule) || $this->checkOptional($element, $_FILES[$element]["name"])) {
				return true;
			}
			else {
				$this->setError($element, $rule["error"]);
				return false;
			}
		}
		else if($rule["type"] == "num") {
			if($this->checkNum($element, $rule) || $this->checkOptional($element)) {
				return true;
			}
			else {
				$this->setError($element, $rule["error"]);
				return false;
			}
		}
		else if($rule["type"] == "email") {
			if($this->checkEmail($element, $rule) || $this->checkOptional($element)) {
				return true;
			}
			else {
				$this->setError($element, $rule["error"]);
				return false;
			}
		}
		else if($rule["type"] == "pwr") {
			if($this->comparePassword($element, $rule) || $this->checkOptional($element)) {
				return true;
			}
			else {
				$this->setError($element, $rule["error"]);
				return false;
			}
		}
		else if($rule["type"] == "arr") {
			if($this->checkArray($element, $rule) || $this->checkOptional($element, count(cleanArray($this->obj->vars[$element])))) {
				return true;
			}
			else {
				$this->setError($element, $rule["error"]);
				return false;
			}
		}
		else if($rule["type"] == "unik") {
			if($this->checkExistance($element, $rule) || $this->checkOptional($element)) {
				return true;
			}
			else {
				$this->setError($element, $rule["error"]);

				message()->addMessage($rule["error"], array("type" => "error"));
				return false;
			}
		}
		else if($rule["type"] == "date") {
			if($this->checkDate($element, $rule) || $this->checkOptional($element)) {
				return true;
			}
			else {
				$this->setError($element, $rule["error"]);
				return false;
			}
		}
		else if($rule["type"] == "timestamp") {
			if($this->checkTimestamp($element, $rule) || $this->checkOptional($element)) {
				return true;
			}
			else {
				$this->setError($element, $rule["error"]);
				return false;
			}
		}
	}

	/**
	* Check if element has an "optional" rule and value is empty
	*
	* @param string $element Element identifier
	* @param string $value Optional value to override internal value array (in case of file upload)
	* @return bool
	*/
	function checkOptional($element, $value=false) {
		$value = $value ? $value : (isset($this->obj->vars) ? $this->obj->vars[$element] : "");
		foreach($this->elements[$element] as $rule) {
			if($rule["type"] == "optional") {
//				print "opt".!$this->obj->vars[$element];
				return !$value;
			}
		}
		return false;
	}

	/**
	* Is file valid?
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function checkFile($element, $rule) {
		if($_FILES[$element]["name"] && $_FILES[$element]["tmp_name"] && !$_FILES[$element]["error"]) {
			return true;
		}

		return false;
	}

	/**
	* Is image valid?
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function checkImage($element, $rule) {
		$width = $this->getRuleDetails($rule, 0);
		$height = $this->getRuleDetails($rule, 1);

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
	function checkString($element, $rule) {
		$string = $this->obj->vars[$element];

		$min_length = $this->getRuleDetails($rule, 0);
		$max_length = $this->getRuleDetails($rule, 1);

		if($string && is_string($string) && (!$min_length || strlen($string) >= $min_length) && (!$max_length || strlen($string) <= $max_length)) {
			return true;
		}
		else {
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
	function checkNum($element, $rule) {
		$string = $this->obj->vars[$element];

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
	function checkEmail($element, $rule) {
		$string = $this->obj->vars[$element];
		if(preg_match('/^[\w\.\-\_]+@[\w-\.]+\.\w{2,4}$/i', $string)) {
			return true;
		}
		else {
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
	function comparePassword($element, $rule) {
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
	function checkArray($element, $rule) {
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
	* Check for other existance of value
	*
	* @param string $element Element identifier
	* @param array $rule Rule array
	* @return bool
	*/
	function checkExistance($element, $rule) {
		$string = $this->obj->vars[$element];

		$db = $this->getRuleDetails($rule, 0);
		$field = $this->getRuleDetails($rule, 1);
		global $id;

		$query = new Query();
		$query->sql("SELECT id FROM $db WHERE ".($field ? $field : $element)." = '$string'");
		if($string && (!$query->count() || $id == $query->result(0, "id"))) {
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
	*/
	function checkDate($element, $rule) {
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
	function checkTimestamp($element, $rule) {
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

	/**
	* Prepare variables in Vars array to be returned to page (because of error or like)
	*/
	function prepareVars() {
		foreach($this->obj->vars as $element => $value) {
			if(is_array($value)) {
				foreach($value as $index => $val) {
					$this->obj->vars[$element][$index] = stripslashes($val);
				}
			}
			else {
				$this->obj->vars[$element] = stripslashes($value);
			}
		}
	}

	/**
	* Set validation indication for element
	*
	* @param string $element Element identifier
	* @param string $indication Indication of validation
	* @return bool
	*/
	function setValidationIndication($element, $indication=false) {
		if(!$this->checkOptional($element)) {
			if(!is_array($this->obj->varnames[$element])) {
				$varname = $this->obj->varnames[$element];
				$this->obj->varnames[$element] = array();
				$this->obj->varnames[$element]['value'] = $varname;
			}
			$this->obj->varnames[$element]['validation'] = $indication ? $indication : "*";
		}
	}

	/**
	* Set error value for element
	*
	* @param string $element Element identifier
	* @param string $error Error message
	* @return bool
	*/
	function setError($element, $error) {
		if(!is_array($this->obj->varnames[$element])) {
			$varname = $this->obj->varnames[$element];
			$this->obj->varnames[$element] = array();
			$this->obj->varnames[$element]['value'] = $varname;
		}
		$this->obj->varnames[$element]['error'] = isset($this->obj->varnames[$element]['error']) && $this->obj->varnames[$element]['error'] ? $this->obj->varnames[$element]['error'].", ".$error : $error;
	}

	/**
	* Remove error from element
	*
	* @param string $element Element identifier
	*/
	function clearError($element) {
		if(!is_array($this->obj->varnames[$element])) {
			$varname = $this->obj->varnames[$element];
			$this->obj->varnames[$element] = array();
			$this->obj->varnames[$element]['value'] = $varname;
		}
		$this->obj->varnames[$element]['error'] = "";
	}

	/**
	* Get rule details from rula args array
	*
	* @param string $rule Rule array
	* @param int $index args index
	* @return string|false
	*/
	function getRuleDetails($rule, $index) {
		if(isset($rule["args"]) && isset($rule["args"][$index])) {
			return $rule["args"][$index];
		}
		else {
			return false;
		}
	}

}

?>