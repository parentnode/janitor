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
	function input($name = false, $options = false) {
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

		if($options !== false) {
			foreach($options as $option => $_value) {
				switch($option) {

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
			if($type == "date") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("max", $max);
				$att_min = $this->attribute("min", $min);

				$_ .= '<input type="date"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_min.$att_pattern.' />';
			}
			else if($type == "datetime") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("max", $max);
				$att_min = $this->attribute("min", $min);

				$_ .= '<input type="datetime"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_min.$att_pattern.' />';
			}
			else if($type == "email") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("max", $max);
				$att_min = $this->attribute("min", $min);

				$_ .= '<input type="email"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_min.$att_pattern.' />';
			}
			else if($type == "tel") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("max", $max);
				$att_min = $this->attribute("min", $min);

				$_ .= '<input type="tel"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_min.$att_pattern.' />';
			}
			else if($type == "password") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("max", $max);
				$att_min = $this->attribute("min", $min);

				$_ .= '<input type="password"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_min.$att_pattern.' />';
			}
			else if($type == "string") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("maxlength", $max);

				$_ .= '<input type="text"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_pattern.' />';
			}
			else if($type == "number" || $type == "integer") {
				$att_value = $this->attribute("value", $value);
				$att_max = $this->attribute("maxlength", $max);

				$_ .= '<input type="number"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_max.$att_pattern.' />';
			}
			else if($type == "files") {

				$_ .= '<input type="file"'.$att_name.$att_id.$att_disabled.$att_pattern.$att_multiple.' />';
			}
			else if($type == "text") {
				$att_max = $this->attribute("maxlength", $max);

				$_ .= '<textarea'.$att_name.$att_id.$att_disabled.$att_readonly.$att_max.'>'.$value.'</textarea>';
			}
			else if($type == "tags") {
				$att_name = $this->attribute("name", $name);

				$_ .= '<input type="text"'.$att_name.$att_id.$att_disabled.$att_readonly.$att_pattern.' />';
			}

			$_ .= '<div'.$this->attribute("class", "hint").'>'.$hint_message.'</div>';
			$_ .= '<div'.$this->attribute("class", "error").'>'.$error_message.'</div>';
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