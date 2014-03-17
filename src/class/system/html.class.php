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
		if(!$type || $type == "geolocation") {
			$currency = $this->getEntityProperty($name, "currency");
			$vatrate = $this->getEntityProperty($name, "vatrate");
		}


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


					case "currency"        : $currency         = $_value; break;
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
			else if($type == "text") {
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

			// PRICES
			else if($type == "prices") {
				$att_name = $this->attribute("name", $name);

				$_ .= '<input type="text"'.$att_name.$att_id.$att_disabled.$att_readonly.$att_pattern.' />';
				$_ .= '<input type="hidden" value="'.$currency.'" name="currency" />';
				$_ .= '<input type="hidden" value="'.$vatrate.'" name="vatrate" />';
			}


			// HINT AND ERROR MESSAGE
			$_ .= '<div'.$this->attribute("class", "help").'>';
				$_ .= '<div'.$this->attribute("class", "hint").'>'.$hint_message.'</div>';
				$_ .= '<div'.$this->attribute("class", "error").'>'.$error_message.'</div>';
			$_ .= '</div>';

		$_ .= '</div>';


		return $_;
	}


	/**
	* Basic input type="submit" element
	*
	* @return string Input element
	*/
	function submit($name = false, $options = false) {
		// print "<p>";
		// print_r($this->data_entities);
		// print "</p>";


	}

	/**
	* Basic input type="button" element
	*
	* @return string Input element
	*/
	function button($name = false, $options = false) {
		// print "<p>";
		// print_r($this->data_entities);
		// print "</p>";


	}

}

?>