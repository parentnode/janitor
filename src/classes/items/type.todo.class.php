<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypeTodo extends Itemtype {


	public $db;
	public $todo_priority;
	public $todo_state;
	private $users;

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		// construct ItemType before adding to model
		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_todo";

		$this->todo_priority = array(0 => "Low", 10 => "Medium", 20 => "High");
		$this->todo_state = array(10 => "Waiting", 30 => "In progress", 20 => "Test", 1 => "Done");


		// find users with todo privileges
		$this->users = array();
		$db_users = SITE_DB.".users";
		$db_access = SITE_DB.".user_access";
		$query = new Query();
		if($query->sql("SELECT users.nickname, users.id FROM ".$db_users." as users, ".$db_access." as access WHERE users.user_group_id = access.user_group_id AND access.controller = '/janitor/admin/todo' AND access.action = '/edit' AND permission = 1 ORDER BY users.nickname")) {
			$this->users = $this->toOptions($query->results(), "id", "nickname");
		}

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
			"label" => "Short SEO description",
			"max" => 155,
			"hint_message" => "Write a short description of the TODO for SEO and listings.",
			"error_message" => "Your TODO needs a description – max 155 characters."
		));

		// Assigned to
		$this->addToModel("user_id", array(
			"type" => "user_id",
			"options" => $this->users,
			"label" => "Assign the task",
			"hint_message" => "Who should take care of this?"
		));

		// Priority
		$this->addToModel("priority", array(
			"type" => "select",
			"options" => $this->todo_priority,
			"label" => "Prioritize the task",
			"hint_message" => "How important is it to get done?",
			"error_message" => "priority error"
		));

		// State
		$this->addToModel("state", array(
			"type" => "select",
			"options" => $this->todo_state,
			"label" => "Current state of progress",
			"hint_message" => "Are you working on this?",
			"error_message" => "priority error"
		));

		// Estimate
		$this->addToModel("estimate", array(
			"type" => "string",
			"label" => "Estimate in hours",
			"hint_message" => "How long will it take to complete this task",
			"error_message" => "estimate error"
		));

		// Deadline
		$this->addToModel("deadline", array(
			"type" => "datetime",
			"label" => "Deadline (yyyy-mm-dd hh:mm:ss)",
			"pattern" => "^[\d]{4}-[\d]{2}-[\d]{2}[0-9\-\/ \:]*$",
			"hint_message" => "Deadline for task", 
			"error_message" => "Date must be of format (yyyy-mm-dd hh:mm:ss)"
		));

	}


	// internal helper functions

	// add todolist tag after save (if todo was created from todolist)
	function saved($item_id) {

		// add todolist tag if created from within todolist
		$return_to_todolist = session()->value("return_to_todolist");
		if($return_to_todolist) {

			$IC = new Items();
			$todolist_tag = $IC->getTags(array("item_id" => $return_to_todolist, "context" => "todolist"));
			if($todolist_tag) {
				$_POST["tags"] = "todolist:".$todolist_tag[0]["value"];
				$this->addTag(array("addTag", $item_id));
			}
		}

		// enable item
		$this->status(array("status", $item_id, 1));
	}

}

?>