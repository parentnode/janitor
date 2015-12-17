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
		$this->db_todos_order = SITE_DB.".item_todolist_todos_order";

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
			"hint_message" => "If you don't know what this is, just leave it empty"
		));

	}


	// CMS SECTION
	// custom loopback function


	// Update item order
	// /janitor/admin/todolist/updateOrder (order comma-separated in POST)
	function updateWishOrder($action) {

		$order_list = getPost("order");
		if(count($action) == 2 && $order_list) {

			$todolist_id = $action[1];

			$query = new Query();
			// make sure type tables exist
			$query->checkDbExistance($this->db_todos_order);

			$order = explode(",", $order_list);
			$sql = "DELETE FROM ".$this->db_todos_order." WHERE item_id = ".$todolist_id;
			$query->sql($sql);


			for($i = 0; $i < count($order); $i++) {
				$todo_id = $order[$i];
				$sql = "INSERT INTO ".$this->db_todos_order." SET position = ".($i+1).", item_id = ".$todolist_id.", todo_id = ".$todo_id;
				$query->sql($sql);
			}

			message()->addMessage("Wish order updated");
			return true;
		}

		message()->addMessage("Wish order could not be updated - please refresh your browser", array("type" => "error"));
		return false;

	}

	// internal helper functions

	// delete todolist tag, when todolist is deleted
	function preDelete($item_id) {

		$IC = new Items();

		$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true)));
		$tag_index = arrayKeyValue($item["tags"], "context", "todolist");
		if($tag_index !== false) {
			// delete todolist tag, when todolist is deleted
			$TC = new Tag();
			$TC->deleteTag(array("deleteTag", $item["tags"][$tag_index]["id"]));
		}

		return true;
	}

	// get todolist tag
	function getTodolistTag($item_id) {

		// TODO: maybe better to use getTags here?
		$IC = new Items();
		$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true)));

		$tag_index = arrayKeyValue($item["tags"], "context", "todolist");
		if($tag_index !== false) {
			$tag = "todolist:".addslashes($item["tags"][$tag_index]["value"]);
		}
		// create tag if todolist doesnt have tag already
		else {
			$tag = "todolist:".$item["name"];
			$_POST["tags"] = $tag;
			$this->addTag(array("addTag", $item["id"]));
		}
		return $tag;
	}

	// get correctly ordered todos for this todolist
	function getOrderedTodos($item_id, $_options = false) {

		$status = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "status"           : $status            = $_value; break;
				}
			}
		}

		// get todolist tag
		$tag = $this->getTodolistTag($item_id);

		$query = new Query();
		$IC = new Items();

		// get all todos (new elements could be added without being ordered yet)
		$todolist_todos = $IC->getItems(array("itemtype" => "todo", "tags" => $tag, "extend" => array("tags" => true, "user" => true)));

//		print_r($todolist_todos);

		// get todo order
		$sql = "SELECT * FROM ".$this->db_todos_order." WHERE item_id = ".$item_id." ORDER BY position";
		$query->sql($sql);
		$ordered_todos = $query->results();

//		print_r($ordered_todos);

		foreach($ordered_todos as $todo_index => $ordered_todo) {
			// ordered todo position in all todos?
			$position = arrayKeyValue($todolist_todos, "id", $ordered_todo["todo_id"]);
			// it is there, so we remove it from the all (and unordered stack)
			if($position !== false) {
				// copy full item to order stack in correct position
				// only allow enabled items

				if($status !== false) {
					// status matches
					if($todolist_todos[$position]["status"] == $status) {
						$ordered_todos[$todo_index] = $todolist_todos[$position];
					}
					// remove from ordered stack
					else {
						unset($ordered_todos[$todo_index]);
					}
				}
				// no status specified
				else {
					$ordered_todos[$todo_index] = $todolist_todos[$position];
				}

				// remove it from full stack
				unset($todolist_todos[$position]);
			}
			// it is not there, so it must have been removed
			else {
				unset($ordered_todos[$todo_index]);
				// remove from ordered list now
				$sql = "DELETE FROM ".$this->db_todos_order." WHERE item_id = ".$item["id"]." AND todo_id = ".$ordered_todo["todo_id"];
				$query->sql($sql);
			}
		}

		// add unordered todos to ordered list
		foreach($todolist_todos as $todolist_todo) {
			$ordered_todos[] = $todolist_todo;
		}

		// return all todos, with the ordered once first
		return $ordered_todos;

	}

}

?>