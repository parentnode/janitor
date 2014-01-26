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

		$this->db_user_groups = SITE_DB.".user_groups";
		$this->db_access = SITE_DB.".user_access";


		// Nickname
		$this->addToModel("nickname", array(
			"type" => "string",
			"label" => "Nickname",
			"required" => true,
			"hint_message" => "Write your nickname or whatever you want us to use to greet you", 
			"error_message" => "Nickname must be filled out"
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
			"hint_message" => "Select user group",
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
			"hint_message" => "Write your mobile number",
			"error_message" => "Invalid number"
		));

		// password
		$this->addToModel("password", array(
			"type" => "password",
			"label" => "Your new password",
			"hint_message" => "Type your new password - must be 8-20 characters",
			"error_message" => "Invalid password"
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


		// User groups Model
		// Usergroup
		$this->addToModel("user_group", array(
			"type" => "string",
			"label" => "Groupname",
			"required" => true,
			"hint_message" => "Name of user group - Admins, customers, etc", 
			"error_message" => "Name must to be filled out"
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
		if($this->validateList(array("nickname", "user_group_id"))) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db);

			$entities = $this->data_entities;
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(user_group_id|nickname)$/", $name)) {
//				if($entity["value"] !== false) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "INSERT INTO ".$this->db." SET " . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
					message()->addMessage("User created");
					return array("item_id" => $query->lastInsertId());
				}
			}
		}

		message()->addMessage("Creating user failed", array("type" => "error"));
		return false;
	}


	// update user
	// /user/update/#user_id#
	// post values
	function update($action) {

		if(count($action) == 2) {
			$user_id = $action[1];
			$query = new Query();

			$entities = $this->data_entities;
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(user_group_id|firstname|lastname|nickname|language)$/", $name)) {
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
		}
		message()->addMessage("Updating user failed", array("type" => "error"));
		return false;
	}


	// TODO: not used due to performance considerations
	function checkUserConstraints($user_id) {
		$query = new Query();
		$user_constraints = array();

		if($query->sql("SELECT * FROM ".UT_ITEMS." WHERE user_id = $user_id")) {
			$user_constraints["items"] = $query->results();
		}

		if(class_exists("Shop")) {
			$shop = new Shop();
			if($query->sql("SELECT * FROM ".$shop->db_orders." WHERE user_id = $user_id")) {
				$user_constraints["orders"] = $query->results();
			}
			if($query->sql("SELECT * FROM ".$shop->db_carts." WHERE user_id = $user_id")) {
				$user_constraints["carts"] = $query->results();
			}
		}
		return $user_constraints;
	}


	// delete user
	// /admin/user/delete/#user_id#
	function delete($action) {
		if(count($action) == 2) {
			$query = new Query();
			if($query->sql("DELETE FROM $this->db WHERE id = ".$action[1])) {
				message()->addMessage("User deleted");
				return true;
			}
			$db_errors = $query->dbError();
			if($db_errors) {
				message()->addMessage("Deleting user failed (".$db_errors.")", array("type" => "error"));
				if(strpos($db_errors, "constraint")) {
					return array("constraint_error" => $db_errors);
				}
				return false;
			}
		}
		message()->addMessage("Deleting user failed", array("type" => "error"));
		return false;
	}


	// disable user
	// /admin/user/disable/#user_id#
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

	// enable user
	// /admin/user/enable/#user_id#
	function enable($action) {
		if(count($action) == 2) {
			$query = new Query();
			if($query->sql("UPDATE $this->db SET status = 1 WHERE id = ".$action[1])) {
				message()->addMessage("User enabled");
				return true;
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



	function setPassword($action) {
		
		$password;
	}

	function resetPassword() {}



	// USER GROUPS

	// get user groups or specific user group
	function getUserGroups($_options=false) {

		$order = "user_group DESC, id DESC";
		$user_group_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "order"           : $order              = $_value; break;
					case "user_group_id"   : $user_group_id      = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific user group
		if($user_group_id) {

			if($query->sql("SELECT * FROM ".$this->db_user_groups." WHERE id = $user_group_id")) {
				return $query->result(0);
			}

		}

		// return all user groups
		else {
			if($query->sql("SELECT * FROM ".$this->db_user_groups." ORDER BY $order")) {
				 return $query->results();
			}
		}

		return false;
	}

	// save user group
	function saveUserGroup() {

		// does values validate
		if($this->validateList(array("user_group"))) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_user_groups);
			$query->checkDbExistance($this->db_access);

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
				$sql = "INSERT INTO ".$this->db_user_groups." SET id = DEFAULT," . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
					message()->addMessage("User group created");
					return array("item_id" => $query->lastInsertId());
				}
			}
		}

		message()->addMessage("User group could not be created", array("type" => "error"));
		return false;

	}

	// update user group
	function updateUserGroup($action) {

		if(count($action) == 2) {

			// does values validate
			$entities = $this->data_entities;
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($this->validateList(array("user_group"), $action[1])) {
				if($values) {
					$query = new Query();
					$sql = "UPDATE ".$this->db_user_groups." SET ".implode(",", $values)." WHERE id = ".$action[1];
//					print $sql;

					if($query->sql($sql)) {
						message()->addMessage("User group updated");
						return array("item_id" => $query->lastInsertId());
					}
				}
			}
		}

		message()->addMessage("User group could not be updated", array("type" => "error"));
		return false;

	}

	// delete user group - 2 parameters exactly
	// /deleteUserGroup/#user_group_id#
	function deleteUserGroup($action) {

		if(count($action) == 2) {

			$query = new Query();
			if($query->sql("DELETE FROM ".$this->db_user_groups." WHERE id = ".$action[1])) {
				message()->addMessage("User group deleted");
				return true;
			}

		}

		message()->addMessage("User group could not be deleted - refresh your browser", array("type" => "error"));
		return false;

	}



	// ACCESS

	// get user groups or specific user group
	function getAccessPoints($_options=false) {

		$user_group_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "user_group_id"   : $user_group_id      = $_value; break;
				}
			}
		}

		// get all controllers
		$fs = new FileSystem();
		$controllers = $fs->files(LOCAL_PATH."/www", array("allow_extensions" => "php"));
		// print_r($controllers);

		$access = array();
		$access["points"] = array();

		// indicate access read state
		$read_access = true;
		foreach($controllers as $controller) {

			include_once($controller);
			if($access_item) {
				// print_r($access_item);

				$access["points"][$controller] = array();
				
				foreach($access_item as $action => $restricted) {
					if($restricted === true) {
						$access["points"][$controller][] = $action;
					}
				}
			}
		}

		// get settings for specific user group id
		if($user_group_id) {

			$access["permissions"] = array();

			$query = new Query();
			// make sure type tables exist
			$query->checkDbExistance($this->db_access);


			if($query->sql("SELECT * FROM ".$this->db_access." WHERE user_group_id = $user_group_id")) {
				$results = $query->results();
				foreach($results as $result) {
					$access["permissions"][$result["action"]] = 1;
				}
			}

		}

		return $access;
	}

	// update user group
	// /updageAccess/#user_group_id#
	// post grants in grants array
	function updateAccess($action) {

		if(count($action) == 2) {

			$query = new Query();
			$grants = getPost("grant");

//			print_r($grants);

			// remove existing grants
			$query->sql("DELETE FROM ".$this->db_access." WHERE user_group_id = " . $action[1]);

			$create_count = 0;
			// set new grants
			if($grants) {
				foreach($grants as $path => $grant) {
					if($grant == 1) {
						if($query->sql("INSERT INTO ".$this->db_access." SET user_group_id = ".$action[1].", action = '$path'")) {
							$create_count++;
						}
					}
					else {
						$create_count++;
					}
				}
			}

			if($create_count == count($grants)) {
				message()->addMessage("Access grants updated");
				return true;
			}
		}

		message()->addMessage("Access grants could not be updated", array("type" => "error"));
		return false;

	}


}

?>