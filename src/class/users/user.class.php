<?php
/**
* @package e-types.items
* This file contains item news maintenance functionality
*/

/**
* TypeNews
*/
class User extends Model {


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		$this->db = SITE_DB.".users";

		// Nickname
		$this->addToModel("nickname", array(
			"type" => "string",
			"label" => "Nickname",
			"required" => true,
			"hint_message" => "Write your nickname or whatever you want us to use to greet you", 
			"error_message" => "Nickname must to be filled out"
		));

		// Firstname
		$this->addToModel("firstname", array(
			"type" => "string",
			"label" => "Firstname",
			"hint_message" => "Write your first- and middlenames",
			"error_message" => "Write your first- and middlenames"
		));

		// Lastname
		$this->addToModel("lastname", array(
			"type" => "string",
			"label" => "Lastname",
			"hint_message" => "Write your lastname",
			"error_message" => "Write your lastname"
		));

		// Usergroup
		$this->addToModel("user_group_id", array(
			"type" => "integer",
			"label" => "User group",
			"hint_message" => "Select user group TODO: Make select with user_groups",
			"error_message" => "Invalid user group"
		));

		// Status
		$this->addToModel("status", array(
			"type" => "integer",
			"label" => "User status",
			"hint_message" => "Enabled/Disabled",
			"error_message" => "Invalid status command"
		));

		// Status
		$this->addToModel("language", array(
			"type" => "string",
			"label" => "Your preferred language",
			"hint_message" => "Select your preferred language",
			"error_message" => "Invalid language"
		));

		parent::__construct();
	}




	// get carts
	// - optional multiple carts, based on content match
	function getUsers($_options=false) {

		// get all carts containing $item_id
		$user_id = false;

		// get carts based on timestamps
		$before = false;
		$after = false;

		$order = "status DESC, id DESC";

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"  : $user_id    = $_value; break;
					case "before"   : $before     = $_value; break;
					case "after"    : $after      = $_value; break;

					case "order"    : $order      = $_value; break;
				}
			}
		}

		$query = new Query();

		// get all carts with item_id in it
		if($user_id) {

			$sql = "SELECT * FROM ".$this->db." WHERE id = $user_id";
//			print $sql;
			if($query->sql($sql)) {
				return $query->result(0);
			}

		}

		// return all carts
		else {
			if($query->sql("SELECT * FROM ".$this->db." ORDER BY $order")) {
				 return $query->results();
			}
		}

		return false;
	}

	
	// save new user
	// gets values from posted model values
	function save() {

		// does values validate
		if($this->validateAll()) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db);

			$entities = $this->data_entities;
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "INSERT INTO ".$this->db." SET id = DEFAULT," . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
					message()->addMessage("User created");
					return true;
				}
			}
			message()->addMessage("Creating user failed", array("type" => "error"));
		}
		return false;
	}

	function update($action) {
		if(count($action) == 2) {
			$user_id = $action[1];
			$query = new Query();

			$entities = $this->data_entities;
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"]) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($this->validateList($names, $user_id)) {
				if($values) {
					$sql = "UPDATE ".$this->db." SET ".implode(",", $values)." WHERE id = ".$user_id;
//					print $sql;
				}

				if(!$values || $query->sql($sql)) {
					message()->addMessage("User updated");
					return true;
				}
			}
			message()->addMessage("Creating user failed", array("type" => "error"));
		}
		return false;
	}

	function delete($action) {
		if(count($action) == 2) {
			$query = new Query();
			if($query->sql("DELETE FROM $this->db WHERE id = ".$action[1])) {
				message()->addMessage("User deleted");
				return true;
			}
			message()->addMessage("Created user failed", array("type" => "error"));
		}
		return false;
	}

	function disable($action) {
		if(count($action) == 2) {
			$query = new Query();
			if($query->sql("UPDATE $this->db SET status = 0 WHERE id = ".$action[1])) {
				message()->addMessage("User disabled");
				return true;
			}
			message()->addMessage("Could not disable user", array("type" => "error"));
		}
		return false;
	}

	function enable($action) {
		if(count($action) == 2) {
			$query = new Query();
			if($query->sql("UPDATE $this->db SET status = 1 WHERE id = ".$action[1])) {
				return true;
				message()->addMessage("User enabled");
			}
			else {
				message()->addMessage("Could not enable user", array("type" => "error"));
			}
		}
		return false;
	}


	function getUsernames($_options) {}

	function addUsername() {}

	function deleteUsername() {}


	function getAddresses($_options) {}

	function addAddress() {
		if(count($action) == 2) {
			$query = new Query();

		}
	}

	function updateAddress() {
		if(count($action) == 2) {
			$query = new Query();

			$posted = getPosts(array());
			print_r($posted);

			//$typeObject->data_entities;
			$names = array();
			$values = array();

			foreach($posted as $name => $value) {
				if($entity["value"]) {
					$names[] = $name;
					$values[] = $name."='".$value."'";
				}
			}

			if($values) {
				$sql = "UPDATE ".$typeObject->db_addresses." SET ".implode(",", $values)." WHERE id = ".$address_id;
//				print $sql;
			}

			if(!$values || $query->sql($sql)) {
				return true;
			}

		}
	}

	function deleteAddress() {}



	function addPassword($password, $type) {}

	function deletePassword() {}

}

?>