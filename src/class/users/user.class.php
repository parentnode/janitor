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
		$this->db_usernames = SITE_DB.".user_usernames";
		$this->db_addresses = SITE_DB.".user_addresses";
		$this->db_passwords = SITE_DB.".user_passwords";
		$this->db_newsletters = SITE_DB.".user_newsletters";


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

		// Language
		$this->addToModel("language", array(
			"type" => "string",
			"label" => "Your preferred language",
			"hint_message" => "Select your preferred language",
			"error_message" => "Invalid language"
		));



		// email
		$this->addToModel("email", array(
			"type" => "email",
			"label" => "Your email",
			"hint_message" => "You can log in using your email",
			"error_message" => "Invalid email"
		));

		// mobile
		$this->addToModel("mobile", array(
			"type" => "tel",
			"label" => "Your mobile",
			"hint_message" => "Write your mobile number ",
			"error_message" => "Invalid number"
		));



		// address label
		$this->addToModel("label", array(
			"type" => "string",
			"label" => "Address label",
			"hint_message" => "Give this address a label (home, office, parents, etc.)",
			"error_message" => "Invalid label"
		));

		// address label
		$this->addToModel("address_name", array(
			"type" => "string",
			"label" => "Name",
			"hint_message" => "Name on door at address",
			"error_message" => "Invalid name"
		));
		// att
		$this->addToModel("att", array(
			"type" => "string",
			"label" => "Att",
			"hint_message" => "Att for address",
			"error_message" => "Invalid att"
		));
		// address 1
		$this->addToModel("address1", array(
			"type" => "string",
			"label" => "Address",
			"hint_message" => "Address",
			"error_message" => "Invalid address"
		));
		// address 2
		$this->addToModel("address2", array(
			"type" => "string",
			"label" => "Additional address",
			"hint_message" => "Additional address info",
			"error_message" => "Invalid address"
		));
		// city
		$this->addToModel("city", array(
			"type" => "string",
			"label" => "City",
			"hint_message" => "Write your city",
			"error_message" => "Invalid city"
		));
		// postal code
		$this->addToModel("postal", array(
			"type" => "string",
			"label" => "Postal code",
			"hint_message" => "Postalcode of your city",
			"error_message" => "Invalid postal code"
		));

		// state
		$this->addToModel("state", array(
			"type" => "string",
			"label" => "State",
			"hint_message" => "Write your state if applicaple",
			"error_message" => "Invalid state"
		));

		// country
		$this->addToModel("country", array(
			"type" => "string",
			"label" => "Country",
			"hint_message" => "Country",
			"error_message" => "Invalid country"
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
				 
				$user = $query->result(0);

				return $user;
			}

		}

		// return all users
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
				if($entity["value"] !== false && preg_match("/^(user_group_id|firstname|lastname|nickname|status|language)$/", $name)) {
//				if($entity["value"] !== false) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "INSERT INTO ".$this->db." SET id = DEFAULT," . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
					message()->addMessage("User created");
					return $query->lastInsertId();
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
				if($entity["value"] !== false && preg_match("/^(user_group_id|firstname|lastname|nickname|status|language)$/", $name)) {
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
			message()->addMessage("Updating user failed", array("type" => "error"));
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


	function getUsernames($_options) {

		$user_id = false;
		$type = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"  : $user_id    = $_value; break;
					case "type"     : $type       = $_value; break;
				}
			}
		}

		$query = new Query();

		if($user_id) {

			if($type) {
				$sql = "SELECT * FROM ".$this->db_usernames." WHERE user_id = $user_id AND type = '$type'";
				if($query->sql($sql)) {
					return $query->result(0);
				}
			}
			else {
				$sql = "SELECT * FROM ".$this->db_usernames." WHERE user_id = $user_id";
				if($query->sql($sql)) {
					return $query->results();
				}
			}

		}

	}

	function getNewsletters($_options) {

		$user_id = false;
		$newsletter = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"        : $user_id          = $_value; break;
					case "newsletter"     : $newsletter       = $_value; break;
				}
			}
		}

		$query = new Query();

		if($user_id) {

			if($newsletter) {
				$sql = "SELECT * FROM ".$this->db_newsletters." WHERE user_id = $user_id AND newsletter = '$newsletter'";
				if($query->sql($sql)) {
					return $query->result(0);
				}
			}
			else {
				$sql = "SELECT * FROM ".$this->db_newsletters." WHERE user_id = $user_id";
				if($query->sql($sql)) {
					return $query->results();
				}
			}

		}

	}

	// create from posted values
	function addUsername($user_id) {

	}

	function deleteUsername() {}


	function getAddresses($_options) {

		$user_id = false;
		$address_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"     : $user_id       = $_value; break;
					case "address_id"  : $address_id    = $_value; break;
				}
			}
		}

		$query = new Query();

		if($user_id) {

			if($type) {
				$sql = "SELECT * FROM ".$this->db_addresses." WHERE address_id = $address_id";
				if($query->sql($sql)) {
					return $query->result(0);
				}
			}
			else {
				$sql = "SELECT * FROM ".$this->db_addresses." WHERE user_id = $user_id";
				if($query->sql($sql)) {
					return $query->results();
				}
			}

		}
		
		
	}

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
				if($entity["value"] && preg_match("/^(user_group_id|firstname|lastname|nickname|status|language)$/", $name)) {
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