<?php
/**
* This file contains HTML-element output functions
* TODO: Implement html.core (this) and let new html.class be the one developers can add customized inputs to
*
*/
class HTML {


	/**
	* Make html tag attribute
	* Attribute values passed as speparate parameters
	* if value is false, it is not added.
	* if attribute has no value, empty string is returned
	* 
	* @param string $attribute_name Name of attribute.
	* @param strings Optional strings to become value of attribute.
	* @return string Complete attribute with value.
	*/
	function attribute($attribute_name) {
		$args = func_get_args();
		$attribute_value = false;
		for($i = 1; $i < count($args); $i++) {
			if($args[$i] !== false && $args[$i] !== "") {
				$attribute_value = $attribute_value !== false ? $attribute_value." ".$args[$i] : $args[$i];
			}
		}
		if($attribute_value !== false && $attribute_value != "") {
			// make sure we don't get illegal chars in value
			return ' '.$attribute_name.'="'.htmlentities(stripslashes(trim($attribute_value)), ENT_QUOTES, "UTF-8").'"';
		}
		else {
			return '';
		}
	}


	/**
	* Convert multidimentional array to options array
	*
	* Objects are typically contained in array of arrays, while selects need simple named array
	* This function facilitates the conversion between the two types
	* TODO: Documentation needed
	*/
	function toOptions($multi_array, $value_index, $text_index) {

		$options = array();
		foreach($multi_array as $array) {
			$options[$array[$value_index]] = $array[$text_index];
		}

		return $options;
	}


	/**
	* WHAT TO DO ABOUT CUSTOM FIELDS
	* Is it separate fields or one field with two inputs? 
	* It gets complicated, what are pros and cons
	* Consider thoroughly before adding a more fields
	* TODO: consider moving custom fields to their own function - would provide better overview and better performance
	*
	* When building model, pros:
	* - declare only one input
	*
	* When building model, cons:
	* - less flexibily in organizing HTML
	* - new variables to set name and value
	*
	*
	* Basic input elements
	*
	* INPUT TYPES:
	* - string
	* - checkbox/boolean
	* - radiobuttons
	* - select
	* - date
	* - datetime
	* - email
	* - tel
	* - password
	* - number
	* - integer
	* - text
	* - html
	* - files
	* - tags
	*
	*
	* @return string Input element
	*/
	function input($name = false, $_options = false) {


		// form security
		if(!isset($this->valid_form_started) || !$this->valid_form_started) {
			return "";
		}

		// Get default settings from model first

		// label
		$label = $this->getProperty($name, "label");

		// type, value and options
		$type = $this->getProperty($name, "type");
		$value = $this->getProperty($name, "value");
		$options = $this->getProperty($name, "options");

		// input state
		$readonly = $this->getProperty($name, "readonly");
		$disabled = $this->getProperty($name, "disabled");

		// frontend stuff
		$class = $this->getProperty($name, "class");
		$id = $this->getProperty($name, "id");

		// validation
		$min = $this->getProperty($name, "min");
		$max = $this->getProperty($name, "max");
		$required = $this->getProperty($name, "required");
		$pattern = $this->getProperty($name, "pattern");

		// tags for HTML editor
		$allowed_tags = $this->getProperty($name, "allowed_tags");

		// visual feedback
		$hint_message = $this->getProperty($name, "hint_message");
		$error_message = $this->getProperty($name, "error_message");


		// overwrite model/defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "label"           : $label            = $_value; break;
					case "type"            : $type             = $_value; break;
					case "value"           : $value            = $_value; break;
					case "options"         : $options          = $_value; break;

					case "readonly"        : $readonly         = $_value; break;
					case "disabled"        : $disabled         = $_value; break;

					case "class"           : $class            = $_value; break;
					case "id"              : $id               = $_value; break;

					case "min"             : $min              = $_value; break;
					case "max"             : $max              = $_value; break;
					case "required"        : $required         = $_value; break;
					case "pattern"         : $pattern          = $_value; break;

					case "allowed_tags"    : $allowed_tags     = $_value; break;

					case "error_message"   : $error_message    = $_value; break;
					case "hint_message"    : $hint_message     = $_value; break;

				}
			}
		}

		// Start generating HTML

		$_ = '';

		$for = stringOr($id, "input_".preg_replace("/\[\]/", "", $name));
		$att_id = $this->attribute("id", $for);
		$att_name = $this->attribute("name", $name);


		// restrictions
		$att_disabled = $disabled ? $this->attribute("disabled", "disabled") : '';
		$att_readonly = $readonly ? $this->attribute("readonly", "readonly") : '';

		// combine classname for field
		$att_class = $this->attribute("class", "field", $type, $class, ($required ? "required" : ""), ($disabled ? "disabled" : ""), ($readonly ? "readonly" : ""), ($min ? "min:".$min : ""), ($max ? "max:".$max : ""), (($allowed_tags && $type == "html") ? "tags:".$allowed_tags : ""));

		// attribute strips value for slashes etc - cannot be used for patterns
		$att_pattern = $pattern ? ' pattern="'.$pattern.'"' : '';

		// multiple selects?
		$att_multiple = $this->attribute("multiple", ($max && $max > 1 ? "multiple" : ""));


		// TODO: temp fix for dlaf
		// built into page-class and create separate output function for html
		if($type == "html" && isset($_options["add-file"]) && isset($_options["delete-file"])) {
			$att_html_add = $this->attribute("data-add-file", $_options["add-file"]);
			$att_html_delete = $this->attribute("data-delete-file", $_options["delete-file"]);
//			$att_html_item_id = $this->attribute("data-item_id", $_options["item_id"]);
		}
		else {
			$att_html_add = "";
			$att_html_delete = "";
//			$att_html_item_id = "";
		}


		// hidden field
		if($type == "hidden") {
			$att_value = $this->attribute("value", $value);
			return '<input type="hidden"'.$att_name.$att_id.$att_value.' />';
		}


		$_ .= '<div'.$att_class.$att_html_add.$att_html_delete.'>';

			// CHECKBOX/BOOLEAN
			// checkboxes have label after input
			if($type == "checkbox" || $type == "boolean") {
				$att_value = $this->attribute("value", "1");
				$att_name = $this->attribute("name", $name);
				$att_checked = $this->attribute("checked", ($value ? "checked" : ""));

				// fallback hidden input so checkbox always sends value (even when not checked)
				$_ .= '<input type="hidden"'.$att_name.' value="0" />';
				$_ .= '<input type="checkbox"'.$att_name.$att_id.$att_value.$att_checked.$att_disabled.$att_readonly.$att_pattern.' />';
			}


			// All other inputs have label in front of input
			// LABEL
			$_ .= '<label'.$this->attribute("for", $for).'>'.$label.'</label>';


			// RADIOBUTTONS
			if($type == "radiobuttons") {

				foreach($options as $radio_value => $radio_label) {

					$radio_for = "input_".preg_replace("/\[\]/", "", $name."_".$radio_value);
					$att_radio_id = $this->attribute("id", $for);
					$att_radio_value = $this->attribute("id", $for);
					$att_radio_checked = $this->attribute("checked", ($value == $radio_value ? "checked" : ""));

					$_ .= '<div class="item">';
						$_ .= '<input type="radio"'.$att_name.$att_radio_id.$att_radio_value.$att_radio_checked.' />';
						$_ .= '<label'.$this->attribute("for", $radio_for).'>'.$radio_label.'</label>';
					$_ .= '</div>';
				}
			}

			// DATE
			if($type == "date") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("max", $max);
				$att_min = $this->attribute("min", $min);

				$_ .= '<input type="date"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_min.$att_pattern.' />';
			}

			// DATETIME
			else if($type == "datetime") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("max", $max);
				$att_min = $this->attribute("min", $min);

				$_ .= '<input type="datetime"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_min.$att_pattern.' />';
			}

			// EMAIL
			else if($type == "email") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("max", $max);
				$att_min = $this->attribute("min", $min);

				$_ .= '<input type="email"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_min.$att_pattern.' />';
			}

			// TEL
			else if($type == "tel") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("max", $max);
				$att_min = $this->attribute("min", $min);

				$_ .= '<input type="tel"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_min.$att_pattern.' />';
			}

			// PASSWORD
			else if($type == "password") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("max", $max);
				$att_min = $this->attribute("min", $min);

				$_ .= '<input type="password"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_min.$att_pattern.' />';
			}

			// STRING
			else if($type == "string") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("maxlength", $max);

				$_ .= '<input type="text"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_pattern.' />';
			}

			// NUMBER OR INTEGER
			else if($type == "number" || $type == "integer") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("maxlength", $max);

				$_ .= '<input type="number"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_pattern.' />';
			}

			// FILES
			else if($type == "files") {

				// add brackets for file input - backend is designed to handle files in array, even if there is just one
				$att_name = $this->attribute("name", $name . "[]");

				$_ .= '<input type="file"'.$att_name.$att_id.$att_disabled.$att_pattern.$att_multiple.' />';
			}

			// TEXT OR HTML
			else if($type == "text" || $type == "html") {
				$att_max = $this->attribute("maxlength", $max);

				$_ .= '<textarea'.$att_name.$att_id.$att_disabled.$att_readonly.$att_max.'>'.$value.'</textarea>';
			}

			// SELECT
			else if($type == "select") {

				$_ .= '<select'.$att_name.$att_id.$att_disabled.$att_readonly.'>';
				foreach($options as $select_option => $select_value) {
					$_ .= '<option value="'.$select_option.'"'.($value == $select_option ? ' selected="selected"' : '').'>'.$select_value.'</option>';
				}
				$_ .= '</select>';
			}

			// TAGS 
			else if($type == "tags") {
				$att_name = $this->attribute("name", $name);

				$_ .= '<input type="text"'.$att_name.$att_id.$att_disabled.$att_readonly.$att_pattern.' />';
			}


			// HINT AND ERROR MESSAGE
			if($hint_message || $error_message) {
				$_ .= '<div'.$this->attribute("class", "help").'>';

					if($hint_message) {
						$_ .= '<div'.$this->attribute("class", "hint").'>'.$hint_message.'</div>';
					}

					if($error_message) {
						$_ .= '<div'.$this->attribute("class", "error").'>'.$error_message.'</div>';
					}

				$_ .= '</div>';
			}

		$_ .= '</div>'."\n";


		return $_;
	}



	/**
	* SPECIAL INPUTS
	* - location (combination of location name, latitude and longitude)
	*/

	function inputPrice($name, $_options = false) {

		// form security
		if(!isset($this->valid_form_started) || !$this->valid_form_started) {
			return "";
		}

		// Get default settings from model first

		// label
		$label = $this->getProperty($name, "label");

		// type, value and options
		$value = $this->getProperty($name, "value");
		$currencies = $this->getProperty($name, "currencies");

		// input state
		$readonly = $this->getProperty($name, "readonly");
		$disabled = $this->getProperty($name, "disabled");

		// frontend stuff
		$class = $this->getProperty($name, "class");
		$id = $this->getProperty($name, "id");

		// validation
		$min = $this->getProperty($name, "min");
		$max = $this->getProperty($name, "max");
		$required = $this->getProperty($name, "required");
		$pattern = $this->getProperty($name, "pattern");

		// visual feedback
		$hint_message = $this->getProperty($name, "hint_message");
		$error_message = $this->getProperty($name, "error_message");


		// price specifics
//		if(!$type || $type == "prices") {
			$vatrate = $this->getProperty($name, "vatrate");
//		}


		// overwrite model/defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "label"           : $label            = $_value; break;
					case "value"           : $value            = $_value; break;
					case "currencies"      : $currencies       = $_value; break;

					case "readonly"        : $readonly         = $_value; break;
					case "disabled"        : $disabled         = $_value; break;

					case "class"           : $class            = $_value; break;
					case "id"              : $id               = $_value; break;

					case "min"             : $min              = $_value; break;
					case "max"             : $max              = $_value; break;
					case "required"        : $required         = $_value; break;
					case "pattern"         : $pattern          = $_value; break;

					case "error_message"   : $error_message    = $_value; break;
					case "hint_message"    : $hint_message     = $_value; break;

					case "vatrate"         : $vatrate          = $_value; break;

				}
			}
		}

		// Start generating HTML

		$_ = '';

		$for = stringOr($id, "input_".$name);
		$att_id = $this->attribute("id", $for);
		$att_name = $this->attribute("name", $name);

		$att_disabled = $disabled ? $this->attribute("disabled", "disabled") : '';
		$att_readonly = $readonly ? $this->attribute("readonly", "readonly") : '';


		// specified in some hardcoded way (via model or template)
		if($currencies) {

			// split currencies
//			if(preg_match("/,/", $currencies)) {
				$currencies = explode(",", $currencies);
//			}
			
		}
		// get all available currencies
		else {

			$query = new Query();
			if($query->sql("SELECT id FROM ".UT_CURRENCIES)) {
				$currencies = $query->results("id");
			}
		}

		$att_class = $this->attribute("class", "field", "price", $class, ($required ? "required" : ""), ($disabled ? "disabled" : ""), ($readonly ? "readonly" : ""), ($min ? "min:".$min : ""), ($max ? "max:".$max : ""));


		// attribute strips value for slashes etc - cannot be used for patterns
		$att_pattern = $pattern ? ' pattern="'.$pattern.'"' : '';


		$_ .= '<div'.$att_class.'>';

			// LABEL
			$_ .= '<label'.$this->attribute("for", $for).'>'.$label.'</label>';

			$att_price_name = $this->attribute("name", $name."[price]");
			$att_currency_name = $this->attribute("name", $name."[currency]");

			$_ .= '<input type="text"'.$att_price_name.$att_id.$att_disabled.$att_readonly.$att_pattern.' />';

			// currency
			$_ .= '<select'.$att_currency_name.' class="currency">';
			foreach($currencies as $currency) {
				$_ .= '<option value="'.$currency.'">'.$currency.'</option>';
			}
			$_ .= '</select>';


			// HINT AND ERROR MESSAGE
			$_ .= '<div'.$this->attribute("class", "help").'>';
				$_ .= '<div'.$this->attribute("class", "hint").'>'.$hint_message.'</div>';
				$_ .= '<div'.$this->attribute("class", "error").'>'.$error_message.'</div>';
			$_ .= '</div>';

		$_ .= '</div>'."\n";


		return $_;

	}



	/**
	* Currently requires three fields created in model
	* TODO: Documentation needed
	*/
	function inputLocation($name_loc = false, $name_lat = false, $name_lon = false, $_options = false) {

		// form security
		if(!isset($this->valid_form_started) || !$this->valid_form_started) {
			return "";
		}

		// labels
		$label_loc = $this->getProperty($name_loc, "label");
		$label_lat = $this->getProperty($name_lat, "label");
		$label_lon = $this->getProperty($name_lon, "label");

		// values
		$value_loc = $this->getProperty($name_loc, "value");
		$value_lat = $this->getProperty($name_lat, "value");
		$value_lon = $this->getProperty($name_lon, "value");

		$required = $this->getProperty($name_loc, "required");

		// visual feedback
		$hint_message = $this->getProperty($name_loc, "hint_message");
		$error_message = $this->getProperty($name_loc, "error_message");


		$class = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "label_loc"       : $label_loc        = $_value; break;
					case "label_lat"       : $label_lat        = $_value; break;
					case "label_lon"       : $label_lon        = $_value; break;

					case "value_loc"       : $value_loc        = $_value; break;
					case "value_lat"       : $value_lat        = $_value; break;
					case "value_lon"       : $value_lon        = $_value; break;

					case "class"           : $class            = $_value; break;

					case "required"        : $required         = $_value; break;

					case "error_message"   : $error_message    = $_value; break;
					case "hint_message"    : $hint_message     = $_value; break;

				}
			}
		}

		// Start generating HTML

		$_ = '';

		$for_loc = "input_".$name_loc;
		$for_lat = "input_".$name_lat;
		$for_lon = "input_".$name_lon;

		$att_for_loc = $this->attribute("for", $for_loc);
		$att_for_lat = $this->attribute("for", $for_lat);
		$att_for_lon = $this->attribute("for", $for_lon);

		$att_id_loc = $this->attribute("id", $for_loc);
		$att_id_lat = $this->attribute("id", $for_lat);
		$att_id_lon = $this->attribute("id", $for_lon);

		$att_name_loc = $this->attribute("name", $name_loc);
		$att_name_lat = $this->attribute("name", $name_lat);
		$att_name_lon = $this->attribute("name", $name_lon);

		$att_value_loc = $this->attribute("value", $value_loc);
		$att_value_lat = $this->attribute("value", $value_lat);
		$att_value_lon = $this->attribute("value", $value_lon);

		$att_class_loc = $this->attribute("class", "location");
		$att_class_lat = $this->attribute("class", "latitude");
		$att_class_lon = $this->attribute("class", "longitude");


		$att_class = $this->attribute("class", "field", "location", $class, ($required ? "required" : ""));


		$_ .= '<div'.$att_class.'>';

			$_ .= '<div'.$this->attribute("class", "location").'>';

				// LOCATION NAME
				$_ .= '<label'.$att_for_loc.'>'.$label_loc.'</label>';
				$_ .= '<input type="text"'.$att_name_loc.$att_value_loc.$att_id_loc.$att_class_loc.' />';

			$_ .= '</div>';

			$_ .= '<div'.$this->attribute("class", "latitude").'>';

				// LATITUDE
				$_ .= '<label'.$att_for_lat.'>'.$label_lat.'</label>';
				$_ .= '<input type="text"'.$att_name_lat.$att_value_lat.$att_id_lat.$att_class_lat.' />';

			$_ .= '</div>';

			$_ .= '<div'.$this->attribute("class", "longitude").'>';

				// LONGITUDE
				$_ .= '<label'.$att_for_lon.'>'.$label_lon.'</label>';
				$_ .= '<input type="text"'.$att_name_lon.$att_value_lon.$att_id_lon.$att_class_lon.' />';

			$_ .= '</div>';


			// HINT AND ERROR MESSAGE
			$_ .= '<div'.$this->attribute("class", "help").'>';
				$_ .= '<div'.$this->attribute("class", "hint").'>'.$hint_message.'</div>';
				$_ .= '<div'.$this->attribute("class", "error").'>'.$error_message.'</div>';
			$_ .= '</div>'."\n";

		$_ .= '</div>'."\n";


		return $_;
	}



	/**
	* Create a simple A HREF link with access validation
	*
	* @param $value String text value for A-tag
	* @param $action String HREF value to be validated
	* @param $_options Array of optional settings
	*/
	function link($value, $action, $_options = false) {

		global $page;
		if(!$page->validatePath($action)) {
			return "";
		}

		$class = false;
		$id = false;
		$target = false;

		$wrapper = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "class"         : $class          = $_value; break;
					case "id"            : $id             = $_value; break;

					case "target"        : $target         = $_value; break;

					case "wrapper"       : $wrapper        = $_value; break;
				}
			}
		}

		$_ = "";

		$att_id = $this->attribute("id", $id);
		$att_class = $this->attribute("class", $class);
		$att_target = $this->attribute("target", $target);

		$att_wrap_id = "";
		$att_wrap_class = "";

		if($wrapper) {
			// with class or id
			if(preg_match("/([a-z]+)[\.#]+/", $wrapper, $node_match)) {
//				print_r($node_match);

				$wrap_node = $node_match[1];

				if(preg_match("/#([a-zA-Z0-9_]+)/", $wrapper, $id_match)) {
//					print_r($id_match);
					$att_wrap_id = $this->attribute("id", $id_match[1]);
				}
				if(preg_match_all("/\.([a-zA-Z0-9_\:]+)/", $wrapper, $class_matches)) {
//					print_r($class_matches);

					$att_wrap_class = $this->attribute("class", implode(" ", $class_matches[1]));
				}
			}
			else {
				$wrap_node = $wrapper;
			}
	
			$_ .= '<'.$wrap_node.$att_wrap_class.$att_wrap_id.'>';
	
		}

		$_ .= '<a href="'.$action.'"'.$att_id.$att_class.$att_target.'>'.$value.'</a>';

		if($wrapper) {
			$_ .= '</'.$wrap_node.'>'."\n";
		}

		return $_;
	}


	/**
	* Start a form tag
	*
	* Will only happen if $action is valid
	* TODO: Documentation needed
	*/
	function formStart($action, $_options = false) {

		global $page;
		if(!$page->validatePath($action)) {
			return "";
		}

		// indicate form state
		$this->valid_form_started = true;

		// default values
		$class = false;
		$id = false;
		$method = "post";
		$target = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "class"         : $class          = $_value; break;
					case "id"            : $id             = $_value; break;

					case "target"        : $target         = $_value; break;

					case "method"       : $method          = $_value; break;
				}
			}
		}

		$_ = "";

		$att_id = $this->attribute("id", $id);
		$att_class = $this->attribute("class", $class);
		$att_target = $this->attribute("target", $target);
		$att_method = $this->attribute("method", $method);
		$att_action = $this->attribute("action", $action);

		$_ .= '<form'.$att_action.$att_method.$att_target.$att_class.$att_id.'>'."\n";
		$_ .= '<input type="hidden" name="csrf-token" value="'.session()->value("csrf").'" />'."\n";


		return $_;
	}

	/**
	* End a form tag
	*
	* Will only happen if Form has been started
	* TODO: Documentation needed
	*/
	function formEnd() {
		
		if(isset($this->valid_form_started) && $this->valid_form_started) {
			$this->valid_form_started = false;
			return '</form>'."\n";
		}
		
	}


	/**
	* Basic input type="button" element
	*
	* @return string Input element
	*/
	function button($value = false, $_options = false) {

		if(!isset($this->valid_form_started) || !$this->valid_form_started) {
			return "";
		}

		$type = "button";
		$name = false;
		$class = false;

		$wrapper = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "type"          : $type           = $_value; break;
					case "name"          : $name           = $_value; break;

					case "class"         : $class          = $_value; break;

					case "wrapper"       : $wrapper        = $_value; break;
				}
			}
		}

		$_ = "";

		$att_value = $this->attribute("value", $value);
		$att_type = $this->attribute("type", $type);
		$att_class = $this->attribute("class", "button", $class);
		$att_name = $this->attribute("name", $name);

		$att_wrap_id = "";
		$att_wrap_class = "";

		if($wrapper) {
			// with class or id
			if(preg_match("/([a-z]+)[\.#]+/", $wrapper, $node_match)) {
//				print_r($node_match);

				$wrap_node = $node_match[1];

				if(preg_match("/#([a-zA-Z0-9_]+)/", $wrapper, $id_match)) {
//					print_r($id_match);
					$att_wrap_id = $this->attribute("id", $id_match[1]);
				}
				if(preg_match_all("/\.([a-zA-Z0-9_\:]+)/", $wrapper, $class_matches)) {
//					print_r($class_matches);
					$att_wrap_class = $this->attribute("class", implode(" ", $class_matches[1]));
				}
			}
			else {
				$wrap_node = $wrapper;
			}
	
			$_ .= '<'.$wrap_node.$att_wrap_class.$att_wrap_id.'>';
	
		}

		$_ .= '<input'.$att_value.$att_name.$att_type.$att_class.' />';

		if($wrapper) {
			$_ .= '</'.$wrap_node.'>'."\n";
		}

		return $_;
	}

	/**
	* Basic input type="submit" element
	*
	* @return string Input element
	*/
	function submit($name = false, $_options = false) {

		$_options["type"] = "submit";
		return $this->button($name, $_options);

	}


	/**
	* Delete item
	*/
	function deleteButton($name, $action, $_options = false) {

		global $page;
		if(!$page->validatePath($action)) {
			return "";
		}

		$js = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "js"           : $js            = $_value; break;

				}
			}
		}

		if($js) {
			$_ = '<li class="delete" data-delete-item="'.$action.'">';
		}
		else {
			$_ = '<li class="delete">';

			$_ .= $this->formStart($action);
			$_ .= '<input type="submit" value="'.$name.'" name="delete" class="button delete" />';
			$_ .= $this->formEnd();
		}

		$_ .= '</li>';

		return $_;
	}


	/**
	* Change status of item
	*/
	function statusButton($enable_label, $disable_label, $action, $item, $_options = false) {

		global $page;
		if(!$page->validatePath($action)) {
			return "";
		}

		$status_states = array(
			0 => "disabled",
			1 => "enabled"
		);

		$js = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "js"                     : $js                      = $_value; break;

				}
			}
		}

		if($item && $item["id"] && isset($item["status"])) {

			$state_class = $status_states[$item["status"]];
			$change_to = ($item["status"]+1)%2;

			if($js) {
				$_ = '<li class="status '.$state_class.'" data-update-status="'.$action.'">';
			}
			else {
				$_ = '<li class="status '.$state_class.'">';

				$_ .= $this->formStart($action.'/'.$item["id"].'/0', array("class" => "disable"));
				$_ .= '<input type="submit" value="'.$disable_label.'" name="disable" class="button status" />';
				$_ .= $this->formEnd();

				$_ .= $this->formStart($action.'/'.$item["id"].'/1', array("class" => "enable"));
				$_ .= '<input type="submit" value="'.$enable_label.'" name="enable" class="button status" />';
				$_ .= $this->formEnd();
			}

			$_ .= '</li>';

		}

		return $_;
	}



}
// create standalone instance to make HTML available without model
$HTML = new HTML();

?>