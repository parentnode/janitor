<?php
/**
* @package janitor.items
* This file contains wishlist maintenance functionality
*/


class TypeTodo extends Model {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		// itemtype database
		$this->db = SITE_DB.".item_todo";

		$this->todo_priority = array(0 => "Low", 5 => "Medium", 10 => "High");
		$this->todo_status = array(0 => "Closed", 1 => "Open");

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"hint_message" => "Name your task - make it meaningful and easy to understand.", 
			"error_message" => "Name must be filled out."
		));

		// Description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Description",
			"hint_message" => "Write a meaningful description of the product. Remember product descriptions are very important for Google - Make sure to use varied language and include all relevant keywords in your description."
		));

		// Priority
		$this->addToModel("priority", array(
			"type" => "select",
			"options" => $this->todo_priority,
//			"options" => array(array(0,"Low"),array(5,"Medium"),array(10,"High")),
			"label" => "Prioritize the task",
			"hint_message" => "How important is it to get done?",
			"error_message" => "priority error"
		));

		// Deadline
		$this->addToModel("deadline", array(
			"type" => "datetime",
			"label" => "Deadline (yyyy-mm-dd hh:mm:ss)",
			"pattern" => "^[\d]{4}-[\d]{2}-[\d]{2}[0-9\-\/ \:]*$",
			"hint_message" => "Deadline for task", 
			"error_message" => "Date must be of format (yyyy-mm-dd hh:mm:ss)"
		));

		// Tags
		$this->addToModel("tags", array(
			"type" => "tags",
			"label" => "Tag",
			"hint_message" => "Start typing to get suggestions. A correct tag has this format: context:value.",
			"error_message" => "Must be correct Tag format."
		));


		parent::__construct();
	}

	// used for frontend communication
	// close
	function close($action) {

		if(count($action) == 2) {

			$IC = new Item();
			if($IC->disableItem($action[1])) {
				return true;
			}

		}
		return false;
	}
	// open
	function open($action) {

		if(count($action) == 2) {

			$IC = new Item();
			if($IC->enableItem($action[1])) {
				return true;
			}

		}
		return false;
	}

}

?>