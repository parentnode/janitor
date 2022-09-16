<?php
/**
* This file contains HTML-element output functions
*/
class HTMLCore {


	function __construct() {

		// current controller path
		$this->path = preg_replace("/\.php$/", "", $_SERVER["SCRIPT_NAME"]);

	}


	// Fill-in, to allow HTML methods to work seemlessly with Model
	function getProperty($name, $property) {return false;}


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
		if($attribute_value !== false && $attribute_value !== "") {
			// make sure we don't get illegal chars in value
			return ' '.$attribute_name.'="'.htmlentities(stripslashes(trim($attribute_value)), ENT_QUOTES, "UTF-8").'"';
		}
		else {
			return '';
		}
	}


	/**
	* Convert multi dimensional array to options array
	*
	* Objects are typically contained in array of arrays, while selects need simple named array
	* This function facilitates the conversion between the two types
	*
	* @param Array $multi_array Multi dimensional Array with datasets
	* @param String $value_index Key of Value to use as option-value
	* @param String $text_index Key of Value to use as option-text
	* @param Array $_options Settings for input
	* @return Array with key/value pair for options
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
		if(is_array($multi_array)) {
			foreach($multi_array as $array) {
				$options[$array[$value_index]] = $array[$text_index];
			}
		}

		return $options;
	}




	// provide media info as classvars for JS
	function jsMedia($item, $variant=false) {

		$IC = new Items();
		$media = $IC->getFirstMedia($item, $variant);

		return $media ? (" format:".$media["format"]." variant:".$media["variant"]) : "";
	}

	// data elements for JS interaction
	// Using default data paths
	function jsData($_filter = false) {

		$_ = '';

		$_ .= ' data-csrf-token="'.session()->value("csrf").'"';

		if(!$_filter || array_search("order", $_filter) !== false) {
			$_ .= ' data-item-order="'.security()->validPath($this->path."/updateOrder").'"'; 
		}

		if(!$_filter || array_search("tags", $_filter) !== false) {
			$_ .= ' data-tag-get="'.security()->validPath("/janitor/admin/items/tags").'"'; 
			$_ .= ' data-tag-delete="'.security()->validPath($this->path."/deleteTag").'"';
			$_ .= ' data-tag-add="'.security()->validPath($this->path."/addTag").'"';
		}

		if(!$_filter || array_search("media", $_filter) !== false) {
			$_ .= ' data-media-order="'.security()->validPath($this->path."/updateMediaOrder").'"';
			$_ .= ' data-media-delete="'.security()->validPath($this->path."/deleteMedia").'"';
			$_ .= ' data-media-name="'.security()->validPath($this->path."/updateMediaName").'"';
		}

		if(!$_filter || array_search("comments", $_filter) !== false) {
			$_ .= ' data-comment-update="'.security()->validPath($this->path."/updateComment").'"';
			$_ .= ' data-comment-delete="'.security()->validPath($this->path."/deleteComment").'"';
		}

		if(!$_filter || array_search("prices", $_filter) !== false) {
			$_ .= ' data-price-delete="'.security()->validPath($this->path."/deletePrice").'"';
		}

		if(!$_filter || array_search("qna", $_filter) !== false) {
			$_ .= ' data-qna-update="'.security()->validPath($this->path."/updateQnA").'"';
			$_ .= ' data-qna-delete="'.security()->validPath($this->path."/deleteQnA").'"';
		}

		return $_;
	}




	/**
	* Start a form tag
	*
	* Will only happen if $action is valid
	*
	* @param String $action Action value for form
	* @param Array $_options Settings for form
	* @return String Start Form element HTML
	*/
	function formStart($action, $_options = false) {

		// relative paths are allowed for ease of use
		// construct absolute path using current controller path
		if(!preg_match("/^\//", $action)) {
			$action = $this->path."/".$action;
		}

		if(!security()->validatePath($action)) {
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
	*
	* @return String End Form element HTML
	*/
	function formEnd() {

		if(isset($this->valid_form_started) && $this->valid_form_started) {
			$this->valid_form_started = false;
			return '</form>'."\n";
		}

	}


	/**
	* Generate HTML for input field
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
	* - json
	* - files
	* - html
	*
	* @param String $name Name of entity or input
	* @param Array $_options Settings for input
	* @return String Input element HTML
	*/
	function input($name, $_options = false) {

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
		$step = $this->getProperty($name, "step");
		$required = $this->getProperty($name, "required");
		$pattern = $this->getProperty($name, "pattern");

		// Compare password with other input
		$compare_to = $this->getProperty($name, "compare_to"); 
 
 
		// tags for HTML editor
		$allowed_tags = $this->getProperty($name, "allowed_tags");

		$file_add = $this->getProperty($name, "file_add");
		$file_delete = $this->getProperty($name, "file_delete");
		$media_add = $this->getProperty($name, "media_add");
		$media_delete = $this->getProperty($name, "media_delete");


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
					case "step"            : $step             = $_value; break;
					case "required"        : $required         = $_value; break;
					case "pattern"         : $pattern          = $_value; break;

					case "compare_to"      : $compare_to       = $_value; break;

					case "error_message"   : $error_message    = $_value; break;
					case "hint_message"    : $hint_message     = $_value; break;


					// HTML specific
					case "allowed_tags"    : $allowed_tags     = $_value; break;
					case "file_add"        : $file_add         = $_value; break;
					case "file_delete"     : $file_delete      = $_value; break;
					case "media_add"       : $media_add        = $_value; break;
					case "media_delete"    : $media_delete     = $_value; break;

				}
			}
		}

		// Start generating HTML

		$_ = '';


		$for = stringOr($id, "input_".preg_replace("/\[|\]/", "", $name));
		$att_id = $this->attribute("id", $for);
		$att_name = $this->attribute("name", $name);


		// Basic restrictions
		$att_disabled = $disabled ? $this->attribute("disabled", "disabled") : '';
		$att_readonly = $readonly ? $this->attribute("readonly", "readonly") : '';
		$att_autocomplete = ($autocomplete || $autocomplete == "on") ? $this->attribute("autocomplete", "on") : $this->attribute("autocomplete", "off");


		// Combine classname for field
		$att_class = $this->attribute("class", 
			"field", 
			$type, 
			$class, 
			($required ? "required" : ""), 
			($disabled ? "disabled" : ""), 
			($readonly ? "readonly" : ""),

			// Url encode min and max values (can be timestamps)
			($min ? "min:".rawurlencode($min) : ""), 
			($max ? "max:".rawurlencode($max) : ""), 

			// Add multiple class for multiple file selects (mostly for CSS targeting)
			(preg_match("/^files$/", $type) && $max && $max > 1 ? "multiple" : ""),
			
			// Allowed tag for HTML field
			(preg_match("/^html$/", $type) ? "tags:".$allowed_tags : "")
		);


		// Special data properties for HTML field
		if($type === "html") {



			// Paths for saving and deleting files
			$att_file_add = $this->attribute("data-file-add", security()->validPath($file_add));
			$att_file_delete = $this->attribute("data-file-delete", security()->validPath($file_delete));
			$att_media_add = $this->attribute("data-media-add", security()->validPath($media_add));
			$att_media_delete = $this->attribute("data-media-delete", security()->validPath($media_delete));

		}

		// Attribute strips value for slashes etc - cannot be used for pattern regex
		$att_pattern = $pattern ? ' pattern="'.$pattern.'"' : '';


		// Multiple files (will only be applied to file input – but could also be used for multiple selects if implemented)
		$att_multiple = $this->attribute("multiple", ($max && $max > 1 ? "multiple" : ""));


		// Hidden field – Keep it short
		if($type === "hidden") {
			$att_value = $this->attribute("value", $value);
			return '<input type="hidden"'.$att_name.$att_id.$att_value.' />';
		}


		// Create field div
		$_ .= '<div'.$att_class.($type === "html" ? ($att_file_add.$att_file_delete.$att_media_add.$att_media_delete) : "").'>';


			// CHECKBOX/BOOLEAN
			// checkboxes have label after input
			if($type === "checkbox" || $type == "boolean") {
				$att_value = $this->attribute("value", "1");
				$att_name = $this->attribute("name", $name);
				$att_checked = $this->attribute("checked", ($value ? "checked" : ""));

				// fallback hidden input so checkbox always sends value (even when not checked)
				$_ .= '<input type="hidden"'.$att_name.' value="0" />';
				$_ .= '<input type="checkbox"'.$att_name.$att_id.$att_value.$att_checked.$att_disabled.$att_readonly.' />';

				// LABEL after input
				$_ .= '<label'.$this->attribute("for", $for).'>'.$label.'</label>';
			}

			// RADIOBUTTONS
			else if($type === "radiobuttons") {

				// LABEL
				$_ .= '<label>'.$label.'</label>';

				foreach($options as $radio_value => $radio_label) {

					$radio_for = $for."_".$radio_value;
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
				if($type === "date") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("max", $max);
					$att_min = $this->attribute("min", $min);

					$_ .= '<input type="date"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.' />';
				}

				// DATETIME
				else if($type === "datetime") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("max", $max);
					$att_min = $this->attribute("min", $min);

					$_ .= '<input type="datetime"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.' />';
				}

				// EMAIL
				else if($type === "email") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("maxlength", stringOr($max, 255));
					$att_min = $this->attribute("minlength", $min);
					$att_compare_to = $this->attribute("data-compare-to", $compare_to);

					$_ .= '<input type="email"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.$att_compare_to.' />';
				}

				// TEL
				else if($type === "tel") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("maxlength", stringOr($max, 255));
					$att_min = $this->attribute("minlength", $min);
					$att_compare_to = $this->attribute("data-compare-to", $compare_to);

					$_ .= '<input type="tel"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.$att_compare_to.' />';
				}

				// PASSWORD
				else if($type === "password") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("maxlength", stringOr($max, 255));
					$att_min = $this->attribute("minlength", $min);
					$att_compare_to = $this->attribute("data-compare-to", $compare_to);

					$_ .= '<input type="password"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.$att_compare_to.' />';
				}

				// STRING
				else if($type === "string") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("maxlength", stringOr($max, 255));
					$att_min = $this->attribute("minlength", $min);
					$att_compare_to = $this->attribute("data-compare-to", $compare_to);

					$_ .= '<input type="text"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.$att_compare_to.' />';
				}

				// NUMBER OR INTEGER
				else if($type === "number" || $type === "integer") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("max", $max);
					$att_min = $this->attribute("min", $min);

					$_ .= '<input type="number"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.' />';
				}

				// RANGE
				else if($type === "range") {
					$att_value = $this->attribute("value", $value);
					$att_max = $this->attribute("max", $max);
					$att_min = $this->attribute("min", $min);
					$att_step = $this->attribute("step", $step);

					$_ .= '<input type="range"'.$att_name.$att_id.$att_value.$att_disabled.$att_readonly.$att_step.$att_max.$att_min.' />';
				}

				// FILES
				else if($type === "files") {

					// add brackets for file input - backend is designed to handle files in array, even if there is just one
					$att_name = $this->attribute("name", $name . "[]");

					// Create accept attribute
					$allowed_formats = $this->getProperty($name, "allowed_formats");
					$att_accept = $this->attribute("accept", $allowed_formats ? ".".implode(",.", explode(",", $allowed_formats)) : "");

					// Image/video size validation rules
					$min_width = $this->getProperty($name, "min_width");
					$att_min_width = $this->attribute("data-min-width", $min_width);

					$min_height = $this->getProperty($name, "min_height");
					$att_min_height = $this->attribute("data-min-height", $min_height);

					$proportions = $this->getProperty($name, "allowed_proportions");
					$att_proportions = $this->attribute("data-allowed-proportions", $proportions);

					$sizes = $this->getProperty($name, "allowed_sizes");
					$att_sizes = $this->attribute("data-allowed-sizes", $sizes);


					// Create file-input
					$_ .= '<input type="file"'.$att_name.$att_id.$att_disabled.$att_multiple.$att_accept.$att_min_width.$att_min_height.$att_proportions.$att_sizes.' />';


					// List files belonging to this input
					$_ .= '<ul class="filelist">';
					if($value) {
						foreach($value as $selected_file) {
							$_ .= '<li class="uploaded media_id:'.$selected_file["id"].' variant:'.$selected_file["variant"].' format:'.$selected_file["format"].' width:'.$selected_file["width"].' height:'.$selected_file["height"].'">'.$selected_file["name"].'</li>';
						}
					}
					$_ .= '</ul>';

				}

				// TEXT
				else if($type === "text") {
					$att_max = $this->attribute("maxlength", $max);
					$att_min = $this->attribute("minlength", $min);

					$_ .= '<textarea'.$att_name.$att_id.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.$att_pattern.'>'.$value.'</textarea>';
				}

				// JSON
				else if($type === "json") {
					$att_max = $this->attribute("maxlength", $max);
					$att_min = $this->attribute("minlength", $min);

					$_ .= '<textarea'.$att_name.$att_id.$att_disabled.$att_readonly.$att_autocomplete.$att_max.$att_min.'>'.$value.'</textarea>';
				}

				// SELECT
				else if($type === "select") {

					$_ .= '<select'.$att_name.$att_id.$att_disabled.$att_readonly.'>';
					foreach($options as $select_option => $select_value) {
						$_ .= '<option value="'.$select_option.'"'.($value == $select_option ? ' selected="selected"' : '').'>'.$select_value.'</option>';
					}
					$_ .= '</select>';
				}

				// HTML
				else if($type === "html") {

					$_ .= '<textarea'.$att_name.$att_id.'>'.htmlentities($value, ENT_COMPAT, "UTF-8").'</textarea>';


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
	* Output input like element, but with p or ul instead of input or select
	*
	* @param String $name Name of entity or input
	* @param Array $_options Settings for input
	* @return String Field element HTML
	*/
	function output($name, $_options = false) {

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


	/**
	* Basic input type="button" element
	*
	* @param String $value Name of input
	* @param Array $_options Settings for button
	* @return string Input element
	*/
	function button($value, $_options = false) {

		if(!isset($this->valid_form_started) || !$this->valid_form_started) {
			return "";
		}

		$type = "button";
		$name = false;
		$formaction = false;
		$class = false;

		$wrapper = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "type"          : $type           = $_value; break;
					case "name"          : $name           = $_value; break;
					case "formaction"    : $formaction     = $_value; break;

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
		$att_formaction = $this->attribute("formaction", $formaction);

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

		$_ .= '<input'.$att_value.$att_name.$att_type.$att_formaction.$att_class.' />';

		if($wrapper) {
			$_ .= '</'.$wrap_node.'>'."\n";
		}

		return $_;
	}

	/**
	* Basic input type="submit" element
	*
	* @param String $value Name of input
	* @param Array $_options Settings for button
	* @return String Input element
	*/
	function submit($value, $_options = false) {

		$_options["type"] = "submit";
		return $this->button($value, $_options);

	}





	/**
	* Confirm button
	*/
	function oneButtonForm($value, $action, $_options = false) {

		// relative paths are allowed for ease of use
		// construct absolute path using current controller path
		if(!preg_match("/^\//", $action)) {
			$action = $this->path."/".$action;
		}

		if(!security()->validatePath($action)) {
			return "";
		}

		$js = false;

		$class = "";
		$name = "confirm";
		$confirm_value = "Confirm";
		$wait_value = false;
		$static = false;

		$dom_submit = false;
		$download = false;
		$target = false;

		$success_location = false;
		$success_function = false;

		$wrapper = "li.confirm";

		$inputs = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "js"                   : $js                     = $_value; break;

					case "class"                : $class                  = $_value; break;
					case "name"                 : $name                   = $_value; break;
					case "confirm-value"        : $confirm_value          = $_value; break;
					case "wait-value"           : $wait_value             = $_value; break;
					case "dom-submit"           : $dom_submit             = $_value; break;
					case "download"             : $download               = $_value; break;
					case "target"               : $target                 = $_value; break;

					case "success-location"     : $success_location       = $_value; break;
					case "success-function"     : $success_function       = $_value; break;

					case "wrapper"              : $wrapper                = $_value; break;
					case "static"               : $static                 = $_value; break;

					case "inputs"               : $inputs                 = $_value; break;

				}
			}
		}


		$_ = "";

		$wrap_node = false;


		$att_wrap_id = "";
		$wrap_class = $static ? "" : "i:oneButtonForm";


		// identify wrapper node/class/id
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
				$wrap_class .= " ".implode(" ", $class_matches[1]);
			}
		}
		else {
			$wrap_node = $wrapper;
		}

		$att_wrap_class = $this->attribute("class", $wrap_class);



		$_ .= '<'.$wrap_node.$att_wrap_class.$att_wrap_id;
		$_ .= ' data-confirm-value="'.$confirm_value.'"';


		if($dom_submit) {
			$_ .= ' data-dom-submit="true"';
		}
		if($download) {
			$_ .= ' data-download="true"';
		}
		// custom waiting value (after submit)
		if($wait_value) {
			$_ .= ' data-wait-value="'.$wait_value.'"';
		}

		if($success_location) {
			$_ .= ' data-success-location="'.$success_location.'"';
		}
		if($success_function) {
			$_ .= ' data-success-function="'.$success_function.'"';
		}

		// JavaScript HTML expansion details
		if($js) {

			$_ .= ' data-button-value="'.$value.'"';
			$_ .= $class ? ' data-button-class="'.$class.'"' : '';
			$_ .= $name ? ' data-button-name="'.$name.'"' : '';
			$_ .= $inputs ? ' data-inputs="'.json_encode($inputs).'"' : '';

			$_ .= ' data-form-action="'.$action.'"';
			$_ .= $target ? ' data-form-target="'.$target.'"' : '';
			$_ .= ' data-csrf-token="'.session()->value("csrf").'"';

		}

		$_ .= '>';


		if(!$js) {
			$att_value = $this->attribute("value", $value);
			$att_type = $this->attribute("type", "submit");
			$att_class = $this->attribute("class", "button", $class);
			$att_name = $this->attribute("name", $name);

			$form_options = [];

			if($target) {
				$form_options["target"] = "_blank";
			}

			$_ .= $this->formStart($action, $form_options);
			if($inputs) {
				foreach($inputs as $name => $value) {
					$_ .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
				}
			}

			$_ .= '<input'.$att_value.$att_name.$att_type.$att_class.' />';
			$_ .= $this->formEnd();
		}


		$_ .= '</'.$wrap_node.'>'."\n";



		//
		// if($js) {
		// 	$_ = '<li class="confirm i:confirmAction'.($class ? " ".$class : "").'"';
		//
		// }
		// else {
		// 	$_ = '<li class="confirm i:confirmAction'.($class ? " ".$class : "").'">';
		//
		// 	$_ .= $this->formStart($action);
		// 	$_ .= '<input type="submit" value="'.$name.'" name="delete" class="button delete" />';
		// 	$_ .= $HTML->formEnd();
		// }
		//
		// $_ .= '</li>';

		return $_;
	}





	// OTHER HTML GENERATING FUNCTIONS


	/**
	* Create a simple A HREF link with access validation
	*
	* @param $value String text value for A-tag
	* @param $action String HREF value to be validated
	* @param $_options Array of optional settings
	* @return String HTML element as string
	*/
	function link($value, $action, $_options = false) {


		// relative paths are allowed for ease of use
		// construct absolute path using current controller path
		if($action && !preg_match("/^\//", $action)) {
			$action = $this->path."/".$action;
		}

		if(!security()->validatePath($action)) {
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

				$wrap_node = $node_match[1];

				if(preg_match("/#([a-zA-Z0-9_]+)/", $wrapper, $id_match)) {
					$att_wrap_id = $this->attribute("id", $id_match[1]);
				}
				if(preg_match_all("/\.([a-zA-Z0-9_\:]+)/", $wrapper, $class_matches)) {
					$att_wrap_class = $this->attribute("class", implode(" ", $class_matches[1]));
				}
			}
			else {
				$wrap_node = $wrapper;
			}
	
			$_ .= '<'.$wrap_node.$att_wrap_class.$att_wrap_id.'>';
	
		}

		$_ .= '<a '.($action ? 'href="'.$action.'"' : '').$att_id.$att_class.$att_target.'>'.$value.'</a>';

		if($wrapper) {
			$_ .= '</'.$wrap_node.'>'."\n";
		}

		return $_;
	}


	/**
	* Create a simple A HREF link with access validation
	*
	* @param $node Array Navigation node data array
	* @return String HTML element as string
	*/
	function navigationLink($node) {

		if(!preg_match("/^http[s]?\:\/\//", $node["link"]) && !security()->validatePath($node["link"])) {
			if($node["fallback"] && security()->validatePath($node["fallback"])) {
				$node["link"] = $node["fallback"];
			} 
			else {
				return "";
			}
		}


		$_ = "";

		$att_class = $this->attribute("class", $node["classname"], $this->selectedNavigation($node["link"]));
		$att_target = $this->attribute("target", $node["target"]);

		$_ .= '<li'.$att_class.'><a href="'.$node["link"].'"'.$att_target.'>'.$node["name"].'</a></li>'."\n";

		return $_;
	}


	/**
	* is link selected in navigation (or part of path)
	*
	* @param String $link Link to check against current path
	* @return String " selected" is link matches, " path" if link is part of current path or empty string
	*/
	function selectedNavigation($link) {

		global $page;

		if($link === $page->url) {
			return "selected";
		}
		else if($link && $link !== "/" && strpos($page->url, $link) !== false) {
			return "path";
		}
		return "";
	}




	// SPECIAL INPUTS

	/**
	* TODO: maybe put these in itemtype.class or html.class 
	* - consider whether they are a set of the Core HTML functionality
	*/
	// html
	// DEPRECATED: Forward to input method for fallback compatibility
	function inputHTML($name, $_options = false) {

		return $this->input($name, $_options);

// 		// Get default settings from model first
//
// 		// label
// 		$label = $this->getProperty($name, "label");
// 		$value = $this->getProperty($name, "value");
//
// 		// frontend stuff
// 		$class = $this->getProperty($name, "class");
// 		$id = $this->getProperty($name, "id");
//
// 		// tags for HTML editor
// 		$allowed_tags = $this->getProperty($name, "allowed_tags");
//
// 		// visual feedback
// 		$hint_message = $this->getProperty($name, "hint_message");
// 		$error_message = $this->getProperty($name, "error_message");
//
//
// 		$file_add = $this->getProperty($name, "file_add");
// 		$file_delete = $this->getProperty($name, "file_delete");
// 		$media_add = $this->getProperty($name, "media_add");
// 		$media_delete = $this->getProperty($name, "media_delete");
//
//
// 		// overwrite model/defaults
// 		if($_options !== false) {
// 			foreach($_options as $_option => $_value) {
// 				switch($_option) {
//
// 					case "label"           : $label            = $_value; break;
// 					case "value"           : $value            = $_value; break;
//
// 					case "class"           : $class            = $_value; break;
// 					case "id"              : $id               = $_value; break;
//
// 					case "allowed_tags"    : $allowed_tags     = $_value; break;
//
// 					case "error_message"   : $error_message    = $_value; break;
// 					case "hint_message"    : $hint_message     = $_value; break;
//
// 					case "file_add"        : $file_add         = $_value; break;
// 					case "file_delete"     : $file_delete      = $_value; break;
//
// 					case "media_add"       : $media_add        = $_value; break;
// 					case "media_delete"    : $media_delete     = $_value; break;
// 				}
// 			}
// 		}
//
//
// 		// Start generating HTML
// 		$_ = '';
//
//
// 		$for = stringOr($id, "input_".$name);
// 		$att_id = $this->attribute("id", $for);
// 		$att_name = $this->attribute("name", $name);
//
// 		$att_class = $this->attribute("class", "field", "html", $class, "tags:".$allowed_tags);
//
// 		// get default paths if not specif
// 		global $page;
// 		if(!$file_add) {
// 			$file_add = security()->validPath($this->path."/addHTMLFile");
// 		}
// 		if(!$file_delete) {
// 			$file_delete = security()->validPath($this->path."/deleteHTMLFile");
// 		}
// 		if(!$media_add) {
// 			$media_add = security()->validPath($this->path."/addHTMLMedia");
// 		}
// 		if(!$media_delete) {
// 			$media_delete = security()->validPath($this->path."/deleteHTMLMedia");
// 		}
//
// 		// paths for saving and deleting files
// 		$att_file_add = $this->attribute("data-file-add", $file_add);
// 		$att_file_delete = $this->attribute("data-file-delete", $file_delete);
// 		$att_media_add = $this->attribute("data-media-add", $media_add);
// 		$att_media_delete = $this->attribute("data-media-delete", $media_delete);
//
// //		print $att_file_add . "," . $att_file_delete;
// //		$att_html_item_id = $this->attribute("data-item_id", $_options["item_id"]);
//
//
// 		$_ .= '<div'.$att_class.$att_file_add.$att_file_delete.$att_media_add.$att_media_delete.'>';
//
// 			$_ .= '<label'.$this->attribute("for", $for).'>'.$label.'</label>';
// 			// entity values in textarea will be interpreted, so double encode them
// 			$_ .= '<textarea'.$att_name.$att_id.'>'.htmlentities($value, ENT_COMPAT, "UTF-8").'</textarea>';
//
// 			// Hint and error message
// 			if($hint_message || $error_message) {
// 				$_ .= '<div'.$this->attribute("class", "help").'>';
// 				if($hint_message) {
// 					$_ .= '<div'.$this->attribute("class", "hint").'>'.$hint_message.'</div>';
// 				}
// 				if($error_message) {
// 					$_ .= '<div'.$this->attribute("class", "error").'>'.$error_message.'</div>';
// 				}
// 				$_ .= '</div>';
// 			}
//
// 		$_ .= '</div>'."\n";
//
// 		return $_;
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




}

?>
