<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypeTodolist extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_todolist";

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"unique" => $this->db,
			"hint_message" => "Name of the list", 
			"error_message" => "List name must be unique"
		));

		// Class
		$this->addToModel("classname", array(
			"type" => "string",
			"label" => "CSS Class for list",
			"hint_message" => "CSS class for custom styling. If you don't know what this is, just leave it empty"
		));

		// Description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Description",
			"hint_message" => "Description for this TODO list"
		));

	}


	// CMS SECTION
	// custom loopback function



	// internal helper functions


	// enable and add tag on save
	function saved($item_id) {

		// enable item
		$this->status(array("status", $item_id, 1));

		// create and add todolist tag
		$name = $this->getProperty("name", "value");
		$tag = "todolist:".addslashes($name);
		$_POST["tags"] = $tag;
		$this->addTag(array("addTag", $item_id));
	}


	// delete todolist tag, when todolist is deleted
	function preDelete($item_id) {

		$IC = new Items();
		$todolist_tag = $IC->getTags(array("item_id" => $item_id, "context" => "todolist"));

		if($todolist_tag) {
			// delete todolist tag, when todolist is deleted
			$TC = new Tag();
			$TC->deleteTag(array("deleteTag", $todolist_tag[0]["id"]));
		}

		return true;
	}

}

?>