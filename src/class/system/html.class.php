<?php
/**
* This file contains HTML-elements
*
*
* Create status input (full with form and everything - will limit HTML in templates)
* Create delete input (full with form and everything - will limit HTML in templates)
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
	* Consider thoroughly before adding a custom field
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
	* Basic input element
	*
	* @return string Input element
	*/
	function input($name = false, $_options = false) {


		// form security
		if(!isset($this->valid_form_started) || !$this->valid_form_started) {
			return "";
		}


		// print "<p>";
		// print_r($this->data_entities);
		// print "</p>";


		// Get default settings from model first

		// label
		$label = $this->getEntityProperty($name, "label");

		// type, value and options
		$type = $this->getEntityProperty($name, "type");
		$value = $this->getEntityProperty($name, "value");
		$options = $this->getEntityProperty($name, "options");

		// input state
		$readonly = $this->getEntityProperty($name, "readonly");
		$disabled = $this->getEntityProperty($name, "disabled");
		$checked = $this->getEntityProperty($name, "checked");

		// frontend stuff
		// TODO: check why class does not get model classname?
		$class = false;
		$id = $this->getEntityProperty($name, "id");

		// validation
		$min = $this->getEntityProperty($name, "min");
		$max = $this->getEntityProperty($name, "max");
		$required = $this->getEntityProperty($name, "required");
		$pattern = $this->getEntityProperty($name, "pattern");

		// visual feedback
		$hint_message = $this->getEntityProperty($name, "hint_message");
		$error_message = $this->getEntityProperty($name, "error_message");


		// custom fields
		// see note above before adding more custom fields

		// price specifics
		// if(!$type || $type == "prices") {
		// 	$currency = $this->getEntityProperty($name, "currency");
		// 	$vatrate = $this->getEntityProperty($name, "vatrate");
		// }


		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "label"           : $label            = $_value; break;
					case "type"            : $type             = $_value; break;
					case "value"           : $value            = $_value; break;
					case "options"         : $options          = $_value; break;

					case "readonly"        : $readonly         = $_value; break;
					case "disabled"        : $disabled         = $_value; break;
					case "checked"         : $checked          = $_value; break;

					case "class"           : $class            = $_value; break;
					case "id"              : $id               = $_value; break;

					case "min"             : $min              = $_value; break;
					case "max"             : $max              = $_value; break;
					case "required"        : $required         = $_value; break;
					case "pattern"         : $pattern          = $_value; break;

					case "error_message"   : $error_message    = $_value; break;
					case "hint_message"    : $hint_message     = $_value; break;


					// case "currency"        : $currency         = $_value; break;
					// case "vatrate"         : $vatrate          = $_value; break;

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

		$att_class = $this->attribute("class", "field", $type, $class, ($required ? "required" : ""), ($disabled ? "disabled" : ""), ($readonly ? "readonly" : ""), ($min ? "min:".$min : ""), ($max ? "max:".$max : ""));


		// attribute strips value for slashes etc - cannot be used for patterns
		$att_pattern = $pattern ? ' pattern="'.$pattern.'"' : '';

		// multiple selects?
		$att_multiple = $this->attribute("multiple", ($max && $max > 1 ? "multiple" : ""));


		// hidden field
		if($type == "hidden") {
			$att_value = $this->attribute("value", $value);
			return '<input type="hidden"'.$att_name.$att_id.$att_value.' />';
		}


		$_ .= '<div'.$att_class.'>';


			// INPUT TYPES:
			// checkboxes and radiobuttons have label after input


			// CHECKBOX/BOOLEAN
			if($type == "checkbox" || $type == "boolean") {
				$att_value = $this->attribute("value", $value);
				$att_name = $this->attribute("name", $name);
				$att_checked = $this->attribute("checked", $checked);

				// fallback hidden input so checkbox always sends value (even when not checked)
				$_ .= '<input type="hidden"'.$att_name.' value="0" />';
				$_ .= '<input type="checkbox"'.$att_name.$att_id.$att_value.$att_checked.$att_disabled.$att_readonly.$att_pattern.' />';
			}


			// TODO: Add radio output
			if($type == "radio") {
				$_ .= 'RADIO BUTTONS NOT IMPLEMENTED IN HTML.CLASS';
			}


			// LABEL
			$_ .= '<label'.$this->attribute("for", $for).'>'.$label.'</label>';


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

				$_ .= '<input type="file"'.$att_name.$att_id.$att_disabled.$att_pattern.$att_multiple.' />';
			}

			// TEXT
			else if($type == "text" || $type == "html") {
				$att_max = $this->attribute("maxlength", $max);

				$_ .= '<textarea'.$att_name.$att_id.$att_disabled.$att_readonly.$att_max.'>'.$value.'</textarea>';
			}


			// SELECT
			// TODO: Refine select output
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

			// // PRICES
			// else if($type == "prices") {
			// 	$att_name = $this->attribute("name", $name);
			//
			// 	$_ .= '<input type="text"'.$att_name.$att_id.$att_disabled.$att_readonly.$att_pattern.' />';
			// 	$_ .= '<input type="hidden" value="'.$currency.'" name="currency" />';
			// 	$_ .= '<input type="hidden" value="'.$vatrate.'" name="vatrate" />';
			// }


			// HINT AND ERROR MESSAGE
			$_ .= '<div'.$this->attribute("class", "help").'>';
				$_ .= '<div'.$this->attribute("class", "hint").'>'.$hint_message.'</div>';
				$_ .= '<div'.$this->attribute("class", "error").'>'.$error_message.'</div>';
			$_ .= '</div>';

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
		$label = $this->getEntityProperty($name, "label");

		// type, value and options
		$value = $this->getEntityProperty($name, "value");
		$currencies = $this->getEntityProperty($name, "currencies");

		// input state
		$readonly = $this->getEntityProperty($name, "readonly");
		$disabled = $this->getEntityProperty($name, "disabled");

		// frontend stuff
		// TODO: check why class does not get model classname? [BECAUSE IT IS NOT TRANSFERRED IN MODEL CLASS - BY WHY NOT?]
		$class = false;
		$id = $this->getEntityProperty($name, "id");

		// validation
		$min = $this->getEntityProperty($name, "min");
		$max = $this->getEntityProperty($name, "max");
		$required = $this->getEntityProperty($name, "required");
		$pattern = $this->getEntityProperty($name, "pattern");

		// visual feedback
		$hint_message = $this->getEntityProperty($name, "hint_message");
		$error_message = $this->getEntityProperty($name, "error_message");


		// price specifics
//		if(!$type || $type == "prices") {
			$vatrate = $this->getEntityProperty($name, "vatrate");
//		}


		// overwrite defaults
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


	function inputLocation($name_loc = false, $name_lat = false, $name_lon = false, $_options = false) {

		// form security
		if(!isset($this->valid_form_started) || !$this->valid_form_started) {
			return "";
		}

		// labels
		$label_loc = $this->getEntityProperty($name_loc, "label");
		$label_lat = $this->getEntityProperty($name_lat, "label");
		$label_lon = $this->getEntityProperty($name_lon, "label");

		// values
		$value_loc = $this->getEntityProperty($name_loc, "value");
		$value_lat = $this->getEntityProperty($name_lat, "value");
		$value_lon = $this->getEntityProperty($name_lon, "value");

		$required = $this->getEntityProperty($name_loc, "required");

		// visual feedback
		$hint_message = $this->getEntityProperty($name_loc, "hint_message");
		$error_message = $this->getEntityProperty($name_loc, "error_message");


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
		if(!$page->validateAction($action)) {
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



	function formStart($action, $_options = false) {

		global $page;
		if(!$page->validateAction($action)) {
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
		if(!$page->validateAction($action)) {
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
		if(!$page->validateAction($action)) {
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




	/* INCLUDING LI WRAPPER */
// 
// 	function action($value, $action, $_options = false) {
// 		global $page;
// 		if(!$page->validateAction($action)) {
// 			return "";
// 		}
// 
// 		$class = false;
// 		$id = false;
// 		$target = false;
// 
// 		$type = "a";
// 		$name = false;
// 
// 		// default li class (could cause havok)
// //		$li_class = superNormalize($value);
// 
// 		// overwrite defaults
// 		if($_options !== false) {
// 			foreach($_options as $_option => $_value) {
// 				switch($_option) {
// 
// 					case "class"         : $class          = $_value; break;
// 					case "id"            : $id             = $_value; break;
// 
// 					case "target"        : $target         = $_value; break;
// 					case "type"          : $type           = $_value; break;
// 					case "name"          : $name           = $_value; break;
// 
// 					case "li_class"      : $li_class       = $_value; break;
// 				}
// 			}
// 		}
// 
// 		$_ = "";
// 
// 		$att_id = $this->attribute("id", $id);
// 		$att_class = $this->attribute("class", $class);
// 
// 		$att_li_class = $this->attribute("class", $li_class);
// 
// 		$_ .= '<li'.$att_li_class.'>';
// 
// 		if($type == "a") {
// 			$att_href = $this->attribute("href", $action);
// 			$att_target = $this->attribute("target", $target);
// 
// 			$_ .= '<a'.$action.$att_id.$att_class.$att_target.'>'.$value.'</a>';
// 		}
// 		else if($type == "submit" || $type == "button") {
// 			$att_value = $this->attribute("value", $value);
// 			$att_name = $this->attribute("name", $name);
// 			$att_type = $this->attribute("type", $type);
// 
// 			$_ .= '<input'.$action.$att_id.$att_class.$att_target.'>'.$value.'</a>';
// 		}
// 
// 		$_ .= '</li>';
// 
// 		return $_;
// 	}
// 
// 
// 
// 	function actionsLinkPrimary($value, $action, $_options = false) {
// 		
// 	}
// 
// 
// 
// 
// 	// Custom Janitor extended input combinations/constructions
// 
// 
// 	// wrapped in li
// 	function actionSubmit() {}
// 
// 	function actionDelete() {}
// 	function actionStatus() {}
// 
// 
// 	// DEPRECATED
// 

	/*
	<li class="status <?= ($item["status"] == 1 ? "enabled" : "disabled") ?>">
		<form class="disable" action="/admin/user/disable/<?= $item["id"] ?>" method="post">
			<input type="submit" class="button status" value="Disable">
		</form>
		<form class="enable" action="/admin/user/enable/<?= $item["id"] ?>" method="post">
			<input type="submit" class="button status" value="Enable">
		</form>
	</li>
	
	<li class="status <?= ($item["status"] == 1 ? "enabled" : "disabled") ?>">
		<form class="disable" action="/admin/user/disable/<?= $item["id"] ?>" method="post">
			<input type="submit" class="button status" value="Disable">
		</form>
		<form class="enable" action="/admin/user/enable/<?= $item["id"] ?>" method="post">
			<input type="submit" class="button status" value="Enable">
		</form>
	</li>
	
	<li class="status <?= ($item["status"] == 1 ? "enabled" : "disabled") ?>"></li>
	<li class="status <?= ($item["status"] == 1 ? "enabled" : "disabled") ?>"></li>
	*/

//	function status($item, $_options = false) {

		// $item = false;
		// 
		// $item_id = false;
		// $status = false;
		// 
		// 
		// 
		// // overwrite defaults
		// if($_options !== false) {
		// 	foreach($_options as $_option => $_value) {
		// 		switch($_option) {
		// 
		// 			case "item"           : $item            = $_value; break;
		// 
		// 			case "item_id"        : $item_id         = $_value; break;
		// 			case "status"         : $status          = $_value; break;
		// 
		// 		}
		// 	}
		// }

		// INCLUDE LI???? OR ADD DIV?
		// OR INJECT PURELY WITH JS? (MAYBE WITH NEW u.f.addField function)
		// <li class="status <//?= ($item["status"] == 1 ? "enabled" : "disabled") ?//>">
		// 
		// 
		// <form action="/admin/cms/disable/<//?= $item["id"] ?//>" class="disable i:formDefaultStatus" method="post" enctype="multipart/form-data">
		// 	<h3>Enabled</h3>
		// 	<input type="submit" value="Disable" class="button status disable" />
		// </form>
		// <form action="/admin/cms/enable/<//?= $item["id"] ?//>" class="enable i:formDefaultStatus" method="post" enctype="multipart/form-data">
		// 	<h3>Disabled</h3>
		// 	<input type="submit" value="Enable" class="button status enable" />
		// </form>



		
//	}


}
// create standalone instance to make HTML available without model
$HTML = new HTML();

?>