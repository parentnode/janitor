<?php
/**
* This file contains HTML-element output functions
* TODO: Implement html.core (this) and let new html.class be the one developers can add customized inputs to
*
*/
class HTMLCore {


	/**
	* Make html tag attribute
	* Attribute values passed as separate parameters
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
	function toOptions($multi_array, $value_index, $text_index, $_options = false) {

		$add = array();
		// overwrite model/defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "add"           : $add            = $_value; break;
				}
			}
		}

		$options = $add;
		
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
	* - files
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
		$autocomplete = $this->getProperty($name, "autocomplete");

		// frontend stuff
		$class = $this->getProperty($name, "class");
		$id = $this->getProperty($name, "id");

		// validation
		$min = $this->getProperty($name, "min");
		$max = $this->getProperty($name, "max");
		$required = $this->getProperty($name, "required");
		$pattern = $this->getProperty($name, "pattern");

		// Compare password with other input
		$compare_to = $this->getProperty($name, "compare_to");

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
					case "autocomplete"    : $autocomplete     = $_value; break;

					case "class"           : $class            = $_value; break;
					case "id"              : $id               = $_value; break;

					case "min"             : $min              = $_value; break;
					case "max"             : $max              = $_value; break;
					case "required"        : $required         = $_value; break;
					case "pattern"         : $pattern          = $_value; break;

					case "compare_to"      : $compare_to       = $_value; break;

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
		$att_autocomplete = ($autocomplete || $autocomplete == "on") ? $this->attribute("autocomplete", "on") : $this->attribute("autocomplete", "off");

		// combine classname for field
		$att_class = $this->attribute("class", "field", $type, $class, ($required ? "required" : ""), ($disabled ? "disabled" : ""), ($readonly ? "readonly" : ""), ($min ? "min:".$min : ""), ($max ? "max:".$max : ""));

		// attribute strips value for slashes etc - cannot be used for patterns
		$att_pattern = $pattern ? ' pattern="'.$pattern.'"' : '';

		// multiple selects/files
		$att_multiple = $this->attribute("multiple", ($max && $max > 1 ? "multiple" : ""));


		// hidden field
		if($type == "hidden") {
			$att_value = $this->attribute("value", $value);
			return '<input type="hidden"'.$att_name.$att_id.$att_value.' />';
		}


		$_ .= '<div'.$att_class.'>';

			// CHECKBOX/BOOLEAN
			// checkboxes have label after input
			if($type == "checkbox" || $type == "boolean") {
				$att_value = $this->attribute("value", "1");
				$att_name = $this->attribute("name", $name);
				$att_checked = $this->attribute("checked", ($value ? "checked" : ""));

				// fallback hidden input so checkbox always sends value (even when not checked)
				$_ .= '<input type="hidden"'.$att_name.' value="0" />';
				$_ .= '<input type="checkbox"'.$att_name.$att_id.$att_value.$att_checked.$att_disabled.$att_readonly.$att_pattern.' />';

				// LABEL after input
				$_ .= '<label'.$this->attribute("for", $for).'>'.$label.'</label>';
			}
			// RADIOBUTTONS
			else if($type == "radiobuttons") {

				// LABEL
				$_ .= '<label>'.$label.'</label>';

				foreach($options as $radio_value => $radio_label) {

					$radio_for = "input_".preg_replace("/\[\]/", "", $name."_".$radio_value);
					$att_radio_id = $this->attribute("id", $radio_for);
					$att_radio_value = $this->attribute("value", $radio_value);
					$att_radio_checked = $this->attribute("checked", ($value == $radio_value ? "checked" : ""));

					$_ .= '<div class="item">';
						$_ .= '<input type="radio"'.$att_name.$att_radio_id.$att_radio_value.$att_radio_checked.' />';
						$_ .= '<label'.$this->attribute("for", $radio_for).'>'.$radio_label.'</label>';
					$_ .= '</div>';
				}
			}
			else {


				// All other inputs have label in front of input
				// LABEL
				$_ .= '<label'.$this->attribute("for", $for).'>'.$label.'</label>';


				// DATE
				if($type == "date") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("max", $max);
					$att_min = $this->attribute("min", $min);

					$_ .= '<input type="date"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.' />';
				}

				// DATETIME
				else if($type == "datetime") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("max", $max);
					$att_min = $this->attribute("min", $min);

					$_ .= '<input type="datetime"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.' />';
				}

				// EMAIL
				else if($type == "email") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("maxlength", stringOr($max, 255));
					$att_min = $this->attribute("minlength", $min);
					$att_compare_to = $this->attribute("data-compare-to", $compare_to);

					$_ .= '<input type="email"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.$att_compare_to.' />';
				}

				// TEL
				else if($type == "tel") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("maxlength", stringOr($max, 255));
					$att_min = $this->attribute("minlength", $min);
					$att_compare_to = $this->attribute("data-compare-to", $compare_to);

					$_ .= '<input type="tel"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.$att_compare_to.' />';
				}

				// PASSWORD
				else if($type == "password") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("maxlength", stringOr($max, 255));
					$att_min = $this->attribute("minlength", $min);
					$att_compare_to = $this->attribute("data-compare-to", $compare_to);

					$_ .= '<input type="password"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.$att_compare_to.' />';
				}

				// STRING
				else if($type == "string") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("maxlength", stringOr($max, 255));
					$att_min = $this->attribute("minlength", $min);

					$_ .= '<input type="text"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.' />';
				}

				// NUMBER OR INTEGER
				else if($type == "number" || $type == "integer") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("max", $max);
					$att_min = $this->attribute("min", $min);

					$_ .= '<input type="number"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.' />';
				}

				// FILES
				else if($type == "files") {

					// add brackets for file input - backend is designed to handle files in array, even if there is just one
					$att_name = $this->attribute("name", $name . "[]");
					$att_class_value = $this->attribute("class", $value ? "uploaded" : "");

					$_ .= '<input type="file"'.$att_name.$att_id.$att_disabled.$att_pattern.$att_multiple.$att_class_value.' />';
				}

				// TEXT
				else if($type == "text") {
					$att_max = $this->attribute("maxlength", $max);
					$att_min = $this->attribute("minlength", $min);

					$_ .= '<textarea'.$att_name.$att_id.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.'>'.$value.'</textarea>';
				}

				// SELECT
				else if($type == "select") {

					$_ .= '<select'.$att_name.$att_id.$att_disabled.$att_readonly.'>';
					foreach($options as $select_option => $select_value) {
						$_ .= '<option value="'.$select_option.'"'.($value == $select_option ? ' selected="selected"' : '').'>'.$select_value.'</option>';
					}
					$_ .= '</select>';
				}

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
	* TODO: maybe put these in itemtype.class or html.class 
	* - consider whether they are a set of the Core HTML functionality
	*/

	// html
	function inputHTML($name, $_options = false) {

		// Get default settings from model first

		// label
		$label = $this->getProperty($name, "label");
		$value = $this->getProperty($name, "value");

		// frontend stuff
		$class = $this->getProperty($name, "class");
		$id = $this->getProperty($name, "id");

		// tags for HTML editor
		$allowed_tags = $this->getProperty($name, "allowed_tags");

		// visual feedback
		$hint_message = $this->getProperty($name, "hint_message");
		$error_message = $this->getProperty($name, "error_message");


		$file_add = $this->getProperty($name, "file_add");
		$file_delete = $this->getProperty($name, "file_delete");
		$media_add = $this->getProperty($name, "media_add");
		$media_delete = $this->getProperty($name, "media_delete");


		// overwrite model/defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "label"           : $label            = $_value; break;
					case "value"           : $value            = $_value; break;

					case "class"           : $class            = $_value; break;
					case "id"              : $id               = $_value; break;

					case "allowed_tags"    : $allowed_tags     = $_value; break;

					case "error_message"   : $error_message    = $_value; break;
					case "hint_message"    : $hint_message     = $_value; break;

					case "file_add"        : $file_add         = $_value; break;
					case "file_delete"     : $file_delete      = $_value; break;

					case "media_add"       : $media_add        = $_value; break;
					case "media_delete"    : $media_delete     = $_value; break;
				}
			}
		}


		// Start generating HTML
		$_ = '';


		$for = stringOr($id, "input_".$name);
		$att_id = $this->attribute("id", $for);
		$att_name = $this->attribute("name", $name);

		$att_class = $this->attribute("class", "field", "html", $class, "tags:".$allowed_tags);

		// get default paths if not specif
		global $page;
		if(!$file_add) {
			$file_add = $page->validPath($this->path."/addHTMLFile");
		}
		if(!$file_delete) {
			$file_delete = $page->validPath($this->path."/deleteHTMLFile");
		}
		if(!$media_add) {
			$media_add = $page->validPath($this->path."/addHTMLMedia");
		}
		if(!$media_delete) {
			$media_delete = $page->validPath($this->path."/deleteHTMLMedia");
		}

		// paths for saving and deleting files
		$att_file_add = $this->attribute("data-file-add", $file_add);
		$att_file_delete = $this->attribute("data-file-delete", $file_delete);
		$att_media_add = $this->attribute("data-media-add", $media_add);
		$att_media_delete = $this->attribute("data-media-delete", $media_delete);

//		print $att_file_add . "," . $att_file_delete;
//		$att_html_item_id = $this->attribute("data-item_id", $_options["item_id"]);


		$_ .= '<div'.$att_class.$att_file_add.$att_file_delete.$att_media_add.$att_media_delete.'>';

			$_ .= '<label'.$this->attribute("for", $for).'>'.$label.'</label>';
			// entity values in textarea will be interpreted, so double encode them
			$_ .= '<textarea'.$att_name.$att_id.'>'.htmlentities($value, ENT_COMPAT, "UTF-8").'</textarea>';

			// Hint and error message
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
	* - location (combination of location name, latitude and longitude)
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
	*
	* @return string Field element
	*/
	function output($name = false, $_options = false) {

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

		// frontend stuff
		$class = $this->getProperty($name, "class");
		$id = $this->getProperty($name, "id");

		// visual feedback
		$hint_message = $this->getProperty($name, "hint_message");


		// overwrite model/defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "label"           : $label            = $_value; break;
					case "type"            : $type             = $_value; break;
					case "value"           : $value            = $_value; break;
					case "options"         : $options          = $_value; break;

					case "class"           : $class            = $_value; break;
					case "id"              : $id               = $_value; break;

					case "hint_message"    : $hint_message     = $_value; break;

				}
			}
		}

		// Start generating HTML

		$_ = '';


		$att_id = $this->attribute("id", $id);
		$att_name = $this->attribute("name", $name);

		// combine classname for field
		$att_class = $this->attribute("class", "field", $type, $class);
		$att_value = $this->attribute("value", $value);


		$_ .= '<div'.$att_class.'>';
			$_ .= '<input type="hidden"'.$att_name.$att_id.$att_value.' />';

			$_ .= '<label>'.$label.'</label>';

			// list
			if($type == "list") {

				$_ .= '<ul'.$att_id.'>';
				foreach($options as $li_option => $li_value) {
					$_ .= '<li class="'.($value == $li_option ? ' selected' : '').'>'.$li_value.'</li>';
				}
				$_ .= '</ul>';

			}
			// paragraph
			else {

				$_ .= '<p'.$att_id.'>'.$value.'</p>';

			}


			// HINT MESSAGE
			if($hint_message) {
				$_ .= '<div'.$this->attribute("class", "help").'>';
					$_ .= '<div'.$this->attribute("class", "hint").'>'.$hint_message.'</div>';
				$_ .= '</div>';
			}


		$_ .= '</div>'."\n";


		return $_;
	}



	// OTHER HTML GENERATING FUNCTIONS


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
	* Create a simple A HREF link with access validation
	*
	* @param $value String text value for A-tag
	* @param $action String HREF value to be validated
	* @param $_options Array of optional settings
	*/
	function navigationLink($node) {

		global $page;
		if(!preg_match("/^http[s]?\:\/\//", $node["link"]) && !$page->validatePath($node["link"])) {
			if($node["fallback"] && $page->validatePath($node["fallback"])) {
				$node["link"] = $node["fallback"];
			} 
			else {
				return "";
			}
		}


		$_ = "";

		$att_class = $this->attribute("class", $node["classname"], ($node["link"] == $page->url ? "selected": ""));
		$att_target = $this->attribute("target", $node["target"]);

		$_ .= '<li'.$att_class.'><a href="'.$node["link"].'"'.$att_target.'>'.$node["name"].'</a></li>';

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

		// relative paths are allowed for ease of use
		// construct absolute path using current controller path
		if(!preg_match("/^\//", $action)) {
			$action = $this->path."/".$action;
		}

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
		$enctype = "application/x-www-form-urlencoded";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "class"         : $class          = $_value; break;
					case "id"            : $id             = $_value; break;

					case "target"        : $target         = $_value; break;

					case "method"        : $method         = $_value; break;
					case "enctype"       : $enctype        = $_value; break;
				}
			}
		}

		$_ = "";

		$att_id = $this->attribute("id", $id);
		$att_class = $this->attribute("class", $class);
		$att_target = $this->attribute("target", $target);
		$att_method = $this->attribute("method", $method);
		$att_action = $this->attribute("action", $action);
		$att_enctype = $this->attribute("enctype", $enctype);

		$_ .= '<form'.$att_action.$att_method.$att_target.$att_class.$att_id.$att_enctype.'>'."\n";
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
	function submit($value = false, $_options = false) {

		$_options["type"] = "submit";
		return $this->button($value, $_options);

	}




	// HTML snippets




}

?>
