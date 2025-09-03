<?php
/**
* @package janitor.items
*/

/**
* This class holds global Tag functionallity.
*
*/

// define default database name constants
// base DB tables


class Tag extends Model {


	public $db;
	public $db_taggings;


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());

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

	}



	// get tag, optionally based on item_id, limited to context, or just check if specific tag exists
	function getTags() {

		$query = new Query();
		$sql = "SELECT tags.id as id, tags.context as context, tags.value as value, count(taggings.id) as tag_count FROM ".UT_TAG." as tags LEFT JOIN ".UT_TAGGINGS."  as taggings ON tags.id = taggings.tag_id GROUP BY tags.id ORDER BY tags.context, tags.value";
//		print $sql;
		if($query->sql($sql)) {
			
			return $query->results();
		}

		return false;
	}


	// add tag globally
 	function API_addTag($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 1 && $this->validateList(["context", "value", "description"])) {

			$context = $this->getProperty("context", "value");
			$value = $this->getProperty("value", "value");
			$description = $this->getProperty("description", "value");

			$result = $this->addTag([
				"context" => $context,
				"value" => $value,
				"description" => $description,
			]);

			if($result === true) {
				message()->addMessage("Tag added.");
				return true;
			}

			else if($result === "EXISTS") {
				message()->addMessage("Tag already exists.");
				return true;
			}

		}

		message()->addMessage("Tag could not be added.", ["type" => "error"]);
		return false;
	}

 	function addTag($_options) {

		$context = false;
		$value = false;
		$description = "";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "context"          : $context           = $_value; break;
					case "value"            : $value             = $_value; break;
					case "description"      : $description       = $_value; break;

				}
			}
		}

		if($context && $value) {

			$query = new Query();

			// Check for existance
			$sql = "SELECT id FROM ".$this->db." WHERE context = '$context' AND value = '$value'";
			if(!$query->sql($sql)) {

				$sql = "INSERT INTO ".$this->db." SET context = '$context', value = '$value', description = '$description'";
				// debug([$sql]);

				if($query->sql($sql)) {
					return true;
				}

			}
			else {
				return "EXISTS";
			}

		}

		return false;

 	}


	// delete tag globally 
 	function API_deleteTag($action) {

		if(count($action) == 2) {

			$tag_id = $action[1];

			$result = $this->deleteTag([
				"tag_id" => $tag_id,
			]);

			if($result === true) {
				message()->addMessage("Tag deleted");
				return true;
			}

		}

		message()->addMessage("Tag could not be deleted.", ["type" => "error"]);
		return false;
	}

 	function deleteTag($_options) {

		$tag_id = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "tag_id"      : $tag_id       = $_value; break;

				}
			}
		}

		if($tag_id) {
			$query = new Query();

			if($query->sql("DELETE FROM ".UT_TAG." WHERE id = $tag_id")) {
				return true;
			}

		}

		return false;
 	}


	// update tag globally
 	function API_updateTag($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2 && $this->validateList(["context", "value", "description"])) {

			$tag_id = $action[1];

			$context = $this->getProperty("context", "value");
			$value = $this->getProperty("value", "value");
			$description = $this->getProperty("description", "value");

			$result = $this->updateTag([
				"tag_id" => $tag_id,
				"context" => $context,
				"value" => $value,
				"description" => $description,
			]);

			if($result === true) {
				message()->addMessage("Tag updated.");
				return true;
			}

		}

		message()->addMessage("Tag could not be updated.", ["type" => "error"]);
		return false;
	}

 	function updateTag($_options) {

		$tag_id = false;
		$context = false;
		$value = false;
		$description = "";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "tag_id"           : $tag_id            = $_value; break;

					case "context"          : $context           = $_value; break;
					case "value"            : $value             = $_value; break;
					case "description"      : $description       = $_value; break;

				}
			}
		}

		if($tag_id && $context && $value) {

			$query = new Query();

			$sql = "UPDATE ".$this->db." SET context = '$context', value = '$value', description = '$description' WHERE id = $tag_id";
			// debug([$sql]);

			if($query->sql($sql)) {
				return true;
			}

		}

		return false;

 	}

}
