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
	* @param string|array $label (Optional) Input label or label array with indexes "error", "class" and "value"
	* @param string $name Input element name.
	* @param string $value (Optional) Input element value.
	* @param string $class (Optional) Input classname. (disabled automatically adds disabled="disabled", readonly automatically adds readonly="readonly").
	* @param string $id (Optional) Input element id.
	* @param string $special (Optional) Special feature like onclick event.
	* @param integer $max_length (Optional) max lenght.
	* @return string Input element
	*
	*
	* Alternate usage:
	* Short hand function from extending objects
	*
	* @param string $index index in object vars
	* @param string $class Input classname. (disabled automatically adds disabled="disabled", readonly automatically adds readonly="readonly").
	* @param string $id (Optional) Input element id.
	* @param integer $max_length (Optional) max lenght.
	* @return string Input element
	*/
	function input($index=false, $class=false, $id=false, $max_length=false, $old_id=false, $old_special=false, $old_max_length=false) {
		$_ = '';


		if(isset($this->varnames)) {

			$for = ($id ? $id : $index);
			$id = $this->makeAttribute("id", ($id ? $id : $index));
			$value = $this->makeAttribute("value", ($this->vars[$index] ? $this->vars[$index] : ''));
			$name = $this->makeAttribute("name", $index);
			$class_att = $this->makeAttribute("class", "text", $class);

			$disabled = strstr($class, "disabled") ? $this->makeAttribute("disabled", $disabled) : '';
			$readonly = strstr($class, "readonly") ? $this->makeAttribute("readonly", $readonly) : '';
			$max_length = $this->makeAttribute("maxlength", $max_length);

			$_ .= $this->label($this->varnames[$index], $for, $class);
			$_ .= '<input type="text"'.$name.$id.$value.$class_att.$disabled.$readonly.$max_length.' />';



			//$label = $this->varnames[$index];
			//print $label;
//			print_r( $this->varnames[$index]);
			return $_;
		}
		
		$label = $index;
		$name = $class;
		$value = $id;
		$class = $max_length;
		$id = $old_id;
		$special = $old_special;
		$max_length = $old_max_length;

		list($label_error, $label_class, $label_value) = $this->makeLabel($label);
		$for = ' for="'.($id ? $id : $name).'"';
		$id = ' id="'.($id ? $id : $name).'"';
//		$value = ' value="'.($value ? $value : '').'"';
		$value = $this->makeAttribute("value", $value);
		$name = ' name="'.$name.'"';
		$disabled = strstr($class, "disabled") ? ' disabled="disabled"' : '';
		$readonly = strstr($class, "readonly") ? ' readonly="readonly"' : '';
		$max_length = $this->makeAttribute("maxlength", $max_length);
		$class = ' class="text'.($class ? " $class" : '').'"';

		// special feature, like onupdate
		$special = $special ? ' '.$special : '';

		$_ .= $label ? '<label'.$for.$label_class.'>'.$label_value.$label_error.'</label>' : '';
		$_ .= '<input type="text"'.$name.$id.$value.$class.$disabled.$readonly.$special.$max_length.' />';
		return $_;
	}


}

?>