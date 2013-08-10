<?php
/**
* @package wires
*/
/**
*
*/

/**
* includes
*/

//include_once("class/basics/itemtype.core.class.php");

/**
* This class holds Item functionallity.
*
*/
class Tag {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {
	}



	// get tag, optionally limited to context, or just check if specific tag exists
	function getTags($options=false) {

		$tag_context = false;
		$tag_value = false;

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "context" : $tag_context = $value; break;
					case "value" : $tag_value = $value; break;
				}
			}
		}

		$query = new Query();
		if($tag_context && $tag_value) {
			return $query->sql("SELECT * FROM ".UT_TAG." as tags, ".UT_TAGGINGS." as taggings WHERE tags.context = '$tag_context' AND tags.value = '$tag_value'");
		}
		else if($tag_context) {
			if($query->sql("SELECT tags.id as id, tags.context as context, tags.value as value FROM ".UT_TAG." as tags, ".UT_TAGGINGS." as taggings WHERE tags.context = '$tag_context'")) {
				return $query->results();
			}
		}
		else {
			if($query->sql("SELECT tags.id as id, tags.context as context, tags.value as value FROM ".UT_TAG." as tags")) {
				return $query->results();
			}
		}
		return false;
	}


}

?>