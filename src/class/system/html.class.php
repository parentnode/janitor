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


}

?>