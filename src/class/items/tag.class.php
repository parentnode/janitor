<?php
/**
* @package janitor.items
*/

/**
* This class holds Tag functionallity.
*
*/

// define default database name constants
// base DB tables


class Tag extends Model {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		$this->db = SITE_DB.".tags";
		$this->db_taggings = SITE_DB.".taggings";

		$this->addToModel("context", array(
				"type" => "string", 
				"label" => "Tag context",
				"required" => true, 
				"hint_message" => "Tag context is the scope/category/relation of the tag",
				"error_message" => "Tag context is always required"
		));

		$this->addToModel("value", array(
				"type" => "string", 
				"label" => "Tag value",
				"required" => true, 
				"hint_message" => "Tag value is the actual value of the tag",
				"error_message" => "Tag context is always required"
		));

		$this->addToModel("description", array(
				"type" => "text", 
				"label" => "Optional description",
				"class" => "autoexpand",
				"hint_message" => "If tag requires any kind of explanation, write it here"
		));

		parent::__construct();
	}



	// get tag, optionally based on item_id, limited to context, or just check if specific tag exists
	function getTags() {

		$query = new Query();

		if($query->sql("SELECT tags.id as id, tags.context as context, tags.value as value FROM ".UT_TAG." ORDER BY tags.context, tags.value")) {
			return $query->results();
		}

		return false;
	}

	// delete tag globally 
 	function deleteTag($action) {

		if(count($action) == 2) {

			$tag_id = $action[1];

			$query = new Query();

			if($query->sql("DELETE FROM ".UT_TAG." WHERE id = $tag_id")) {
				message()->addMessage("Tag deleted");
				return true;
			}
		}
		message()->addMessage("Tag could not be deleted", array("type" => "error"));
		return false;
 	}

	// update tag globally
 	function updateTag($action) {

		if(count($action) == 2) {
			$tag_id = $action[1];
			$query = new Query();

			$entities = $this->data_entities;
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(context|value|description)$/", $name)) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($this->validateList($names, $tag_id)) {
				if($values) {
					$sql = "UPDATE ".$this->db." SET ".implode(",", $values)." WHERE id = ".$tag_id;

//					print $sql;
				}

				if(!$values || $query->sql($sql)) {
					message()->addMessage("Tag updated");
					return true;
				}
			}
		}
		message()->addMessage("Updating tag failed", array("type" => "error"));
		return false;

 	}

}

?>