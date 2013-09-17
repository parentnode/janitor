<?php
/**
* This file contains HTML-elements
*
*
*/

class HTML {

	/**
	* Make html tag attribute
	* Classes passed as speparate parameters
	* if class = false, it is not added.
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
	* Basic input element
	*
	* @return string Input element
	*/
	function input($name = false, $_options = false) {
		// print "<p>";
		// print_r($this->data_entities);
		// print "</p>";

		$type = $this->getEntityProperty($name, "type");
		$value = $this->getEntityProperty($name, "value");
		$label = $this->getEntityProperty($name, "label");

		$class = false;
		$id = $this->getEntityProperty($name, "id");
		$min = $this->getEntityProperty($name, "min");
		$max = $this->getEntityProperty($name, "max");
		$required = $this->getEntityProperty($name, "required");
		$pattern = $this->getEntityProperty($name, "pattern");

		$hint_message = $this->getEntityProperty($name, "hint_message");
		$error_message = $this->getEntityProperty($name, "error_message");

		// price specifics
		$currency = $this->getEntityProperty($name, "currency");
		$vatrate = $this->getEntityProperty($name, "vatrate");


		$options = $this->getEntityProperty($name, "options");

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "type"            : $type             = $_value; break;
					case "value"           : $value            = $_value; break;
					case "label"           : $label            = $_value; break;
					case "required"        : $required         = $_value; break;
					case "pattern"         : $pattern          = $_value; break;

					case "min"             : $min              = $_value; break;
					case "max"             : $max              = $_value; break;

					case "error_message"   : $error_message    = $_value; break;
					case "hint_message"    : $hint_message     = $_value; break;

					case "class"           : $class            = $_value; break;
					case "id"              : $id               = $_value; break;

					case "currency"        : $currency         = $_value; break;
					case "vatrate"         : $vatrate          = $_value; break;

					case "options"         : $options          = $_value; break;

				}
			}
		}


		$_ = '';

		$for = stringOr($id, "input_".$name);
		$att_id = $this->attribute("id", $for);

		$att_name = $this->attribute("name", $name);

		$att_disabled = strstr($class, "disabled") ? $this->attribute("disabled", "disabled") : '';
		$att_readonly = strstr($class, "readonly") ? $this->attribute("readonly", "readonly") : '';


		$att_class = $this->attribute("class", "field", $type, $class, ($required ? "required" : ""), ($min ? "min:".$min : ""), ($max ? "max:".$max : ""));

		$att_pattern = $this->attribute("pattern", $pattern);
		$att_multiple = $this->attribute("multiple", ($max && $max > 1 ? "multiple" : ""));

		// strings length


		$_ .= '<div'.$att_class.'>';
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

			// SELECT
			// TODO: Refine select output
			// TODO: Add radio output
			// TODO: Add checkbox output
			else if($type == "select") {

				$_ .= '<select'.$att_name.$att_id.$att_disabled.$att_readonly.'>';
				foreach($options as $option) {
					$_ .= '<option value="'.$option[0].'"'.($value == $option[0] ? ' selected="selected"' : '').'>'.$option[1].'</option>';
				}
				$_ .= '</select>';
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