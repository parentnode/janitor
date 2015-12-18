<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*/

class TypeTodo extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		// construct ItemType before adding to model
		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_todo";

		$this->todo_priority = array(0 => "Hold", 1 => "Low", 2 => "Medium", 3 => "High");
		$this->todo_status = array(0 => "Closed", 1 => "Open");
		

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
			"label" => "Description",
			"hint_message" => "Write a meaningful description of the TODO."
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

		// Deadline
		$this->addToModel("deadline", array(
			"type" => "datetime",
			"label" => "Deadline (yyyy-mm-dd hh:mm:ss)",
			"pattern" => "^[\d]{4}-[\d]{2}-[\d]{2}[0-9\-\/ \:]*$",
			"hint_message" => "Deadline for task", 
			"error_message" => "Date must be of format (yyyy-mm-dd hh:mm:ss)"
		));

	}



	// add todolist tag after save (if todo was created from todolist)
	function postSave($item_id) {

		$IC = new Items();

		$return_to_todolist = session()->value("return_to_todolist");
		if($return_to_todolist) {
			$model_todolist = $IC->typeObject("todolist");
			$todolist_tag = $model_todolist->getTodolistTag($return_to_todolist);

			$_POST["tags"] = $todolist_tag;
			$this->addTag(array("addTag", $item_id));
		}

		// enable item
		$this->status(array("status", $item_id, 1));
	}


	// CMS SECTION
	// custom loopback function

	// used for frontend communication

	// close task
	// /janitor/todo/close/#item_id#
	function close($action) {

		if(count($action) == 2) {

			$IC = new Items();
			if($IC->status($action[1], 0)) {
				return true;
			}

		}
		return false;
	}

	// open
	// /janitor/todo/open/#item_id#
	function open($action) {

		if(count($action) == 2) {

			$IC = new Items();
			if($IC->status($action[1], 1)) {
				return true;
			}

		}
		return false;
	}

}

?>