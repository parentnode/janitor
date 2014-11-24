<?php
/**
* @package janitor.users
* This file contains Admin User functionality
*/

class SuperUser extends User {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		// Extended privilege user tables
		$this->db_user_groups = SITE_DB.".user_groups";
		$this->db_access = SITE_DB.".user_access";


		// Usergroup
		$this->addToModel("user_group_id", array(
			"type" => "integer",
			"label" => "User group",
			"required" => true,
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


		// User groups Model
		// Usergroup
		$this->addToModel("user_group", array(
			"type" => "string",
			"label" => "Groupname",
			"required" => true,
			"hint_message" => "Name of user group - Admins, customers, etc", 
			"error_message" => "Name must to be filled out"
		));

	}



	// save new user
	// /janitor/admin/user/save (values in POST)
	function save($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("nickname", "user_group_id"))) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db);

			// get entities for current value
			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(user_group_id|nickname|firstname|lastname|language|status)$/", $name)) {
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
	// /janitor/admin/user/update/#user_id# (values in POST)
	function update($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2) {
			$user_id = $action[1];
			$query = new Query();

			$entities = $this->getModel();
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

	// Change user status
	// /janitor/admin/user/status/#user_id#/#status#
	function status($action) {

		$status_states = array(
			0 => "disabled",
			1 => "enabled"
		);

		if(count($action) == 3 && isset($status_states[$action[2]])) {
		
			$query = new Query();

			// update status for user
			if($query->sql("SELECT id FROM ".$this->db." WHERE id = ".$action[1])) {
				$query->sql("UPDATE ".$this->db." SET status = ".$action[2]." WHERE id = ".$action[1]);

				message()->addMessage("User ".$status_states[$action[2]]);
				return true;
			}
			message()->addMessage("User could not be ".$status_states[$action[2]], array("type" => "error"));

		}
		return false;

	}

	// delete user
	// /janitor/admin/user/delete/#user_id#
	// TODO: Extend constraint detection
	function delete($action) {

		if(count($action) == 2) {
			$query = new Query();
			$sql = "DELETE FROM $this->db WHERE id = ".$action[1];
//			print $sql;
			if($query->sql($sql)) {
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



	// TODO: not used due to performance considerations
	// might be reimplemented as this action will only happen rarely (when a user is deleted)
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


	


	/**
	* Get users
	*
	* get all users
	* Get all users in user_group
	* Get specific user_id
	* Get users with email as username
	* Get users with mobile as username
	*/
	function getUsers($_options=false) {

		// default values
		$user_id = false;
		$user_group_id = false;
		$order = "status DESC, id DESC";

		$email = false;
		$mobile = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "user_group_id"  : $user_group_id    = $_value; break;
					case "user_id"        : $user_id          = $_value; break;
					case "order"          : $order            = $_value; break;

					case "email"          : $email            = $_value; break;
					case "mobile"         : $mobile           = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific user
		if($user_id) {

			$sql = "SELECT * FROM ".$this->db." WHERE id = $user_id";
//			print $sql;
			if($query->sql($sql)) {
				$user = $query->result(0);
				return $user;
			}
		}

		// get users for user_group
		else if($user_group_id) {

			$sql = "SELECT * FROM ".$this->db." WHERE user_group_id = $user_group_id";
//			print $sql;
			if($query->sql($sql)) {
				$users = $query->results();
				return $users;
			}
		}

		// get users with email as username
		else if($email) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = 'email' AND username = '$email'";
//			print $sql;
			if($query->sql($sql)) {
				return $query->results();
			}
		}
		// get users with mobile as username
		else if($mobile) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = 'mobile' AND username = '$mobile'";
//			print $sql;
			if($query->sql($sql)) {
				return $query->results();
			}
		}

		// return all users
		else if(!isset($_options["user_id"]) && !isset($_options["user_id"]) && !isset($_options["user_group_id"]) && !isset($_options["email"]) && !isset($_options["mobile"])) {
			if($query->sql("SELECT * FROM ".$this->db." ORDER BY $order")) {
				 return $query->results();
			}
		}

		return false;
	}



	// USERNAMES

	// get usernames or specific username
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

			// return specific username
			if($type) {
				$sql = "SELECT * FROM ".$this->db_usernames." WHERE user_id = $user_id AND type = '$type'";
				if($query->sql($sql)) {
					return $query->result(0, "username");
				}
				return false;
			}
			// return all usernames for user
			else {
				$sql = "SELECT * FROM ".$this->db_usernames." WHERE user_id = $user_id";
				if($query->sql($sql)) {
					return $query->results();
				}
			}

		}

		return false;
	}

	// Update usernames from posted values
	function updateUsernames($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		if(count($action) == 2) {

			$user_id = $action[1];
			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_usernames);

			$entities = $this->getModel();

			$email = $this->getProperty("email", "value");
			$mobile = $this->getProperty("mobile", "value");


			$mobile_exists = false;
			$email_exists = false;

			// check if email exists
			if($email) {
				$email_exists_for_user_id = $this->matchUsernames(array("email" => $email));
				if($email_exists_for_user_id && $email_exists_for_user_id["user_id"] != $user_id) {
					$email_exists = true;
					message()->addMessage("Email already exists", array("type" => "error"));
				}
			}
			// check if mobile exists
			if($mobile) {
				$mobile_exists_for_user_id = $this->matchUsernames(array("mobile" => $mobile));
				if($mobile_exists_for_user_id && $mobile_exists_for_user_id["user_id"] != $user_id) {
					$mobile_exists = true;
					message()->addMessage("Mobile already exists", array("type" => "error"));
				}
			}

			if($mobile_exists || $email_exists) {
				return false;
			}


			$current_email = $this->getUsernames(array("user_id" => $user_id, "type" => "email"));
			$current_mobile = $this->getUsernames(array("user_id" => $user_id, "type" => "mobile"));

			// email does not exist
			if($email && !$current_email) {
				$sql = "INSERT INTO $this->db_usernames SET username = '$email', verified = 0, type = 'email', user_id = $user_id";
//				print $sql."<br>";
				if($query->sql($sql)) {
					message()->addMessage("Email added");
				}
				else {
					message()->addMessage("Could not add email", array("type" => "error"));
				}
			}
			// email should be deleted?
			else if(!$email && $current_email !== false) {

				$sql = "DELETE FROM $this->db_usernames WHERE type = 'email' AND user_id = $user_id";
//				print $sql."<br>";
				if($query->sql($sql)) {
					message()->addMessage("Email deleted");
				}
				else {
					message()->addMessage("Could not delete email", array("type" => "error"));
				}
			}
			// email is changed?
			else if($email != $current_email) {

				$sql = "UPDATE $this->db_usernames SET username = '$email', verified = 0 WHERE type = 'email' AND user_id = $user_id";
//				print $sql."<br>";
				if($query->sql($sql)) {
					message()->addMessage("Email updated");
				}
				else {
					message()->addMessage("Could not update email", array("type" => "error"));
				}
			}

			// mobile does not exist
			if($mobile && !$current_mobile) {
				$sql = "INSERT INTO $this->db_usernames SET username = '$mobile', verified = 0, type = 'mobile', user_id = $user_id";
//				print $sql."<br>";
				if($query->sql($sql)) {
					message()->addMessage("Mobile added");
				}
				else {
					message()->addMessage("Could not add mobile", array("type" => "error"));
				}
			}
			// is mobile changed?
			else if(!$mobile && $current_mobile != false) {

				$sql = "DELETE FROM $this->db_usernames WHERE type = 'mobile' AND user_id = $user_id";
//				print $sql."<br>";
				if($query->sql($sql)) {
					message()->addMessage("Mobile deleted");
				}
				else {
					message()->addMessage("Could not delete mobile", array("type" => "error"));
				}
			}
			// mobile is changed?
			else if($mobile != $current_mobile) {

				$sql = "UPDATE $this->db_usernames SET username = '$mobile', verified = 0 WHERE type = 'mobile' AND user_id = $user_id";
//				print $sql."<br>";
				if($query->sql($sql)) {
					message()->addMessage("Mobile updated");
				}
				else {
					message()->addMessage("Could not update mobile", array("type" => "error"));
				}
			}

		}
		return true;
	}

	// NOT NEEDED YET AS updateUsernames also reset usernames
	function deleteUsername() {}



	// PASSWORD

	// check if password exists
	function hasPassword($user_id) {

		$query = new Query();

		$sql = "SELECT id FROM ".$this->db_passwords." WHERE user_id = $user_id";
		if($query->sql($sql)) {
			return true;
		}
		return false;
	}

	// set new password for user
	// user/setPassword/#user_id#
	function setPassword($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2) {

			// does values validate
			if($this->validateList(array("password"))) {

				$user_id = $action[1];
				$query = new Query();

				// make sure type tables exist
				$query->checkDbExistance($this->db_passwords);

				$password = sha1($this->getProperty("password", "value"));
				if($this->hasPassword($user_id)) {
					$sql = "UPDATE ".$this->db_passwords." SET password = '$password' WHERE user_id = $user_id";
				}
				else {
					$sql = "INSERT INTO ".$this->db_passwords." SET user_id = $user_id, password = '$password'";
				}
				if($query->sql($sql)) {
					message()->addMessage("password saved");
					return true;
				}
			}
		}

		message()->addMessage("Password could not be saved", array("type" => "error"));
		return false;
	}

	// TODO: reset password needs to be implemented
	// start reset password procedure
	function resetPassword() {}



	// ADDRESSES

	// return addresses
	// can return all addresses for a user, or a specific address
	// Adds country_name for stored country ISO value
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
		global $page;
		$countries = $page->countries();

		// get addresses for user
		if($user_id) {

			$sql = "SELECT * FROM ".$this->db_addresses." WHERE user_id = $user_id";
//			print $sql;

			if($query->sql($sql)) {
				$results = $query->results();
				foreach($results as $index => $result) {
					$results[$index]["country_name"] = $countries[arrayKeyValue($countries, "id", $result["country"])]["name"];
				}
				return $results;
			}

		}
		// get specific address
		else if($address_id) {
			$sql = "SELECT * FROM ".$this->db_addresses." WHERE id = $address_id";
//			print $sql;

			if($query->sql($sql)) {
				$result = $query->result(0);
				$result["country_name"] = $countries[arrayKeyValue($countries, "id", $result["country"])]["name"];
				return $result;
			}
		}
	}

	// create a new address
	// /janitor/admin/user/addAddress/#user_id# (values in POST)
	function addAddress($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2 && $this->validateList(array("address_label","address_name","address1","postal","city","country"))) {

			$query = new Query();
			$user_id = $action[1];

			// make sure type tables exist
			$query->checkDbExistance($this->db_addresses);

			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(address_label|address_name|att|address1|address2|city|postal|state|country)$/", $name)) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "INSERT INTO ".$this->db_addresses." SET user_id=$user_id,modified_at=CURRENT_TIMESTAMP," . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
					message()->addMessage("Address created");
					return array("item_id" => $user_id);
				}
			}
		}
	}

	// update an address
	// /janitor/admin/user/updateAddress/#address_id# (values in POST)
	function updateAddress($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2) {
			$query = new Query();
			$address_id = $action[1];

			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "UPDATE ".$this->db_addresses." SET ".implode(",", $values)." WHERE id = ".$address_id;
//				print $sql;
			}

			if(!$values || $query->sql($sql)) {
				message()->addMessage("Address updated");
				return true;
			}

		}

		message()->addMessage("Address could not be updated", array("type" => "error"));
		return false;
	}

	// Delete address
	// /janitor/admin/user/deleteAddress/#address_id#
	function deleteAddress($action) {
		
		if(count($action) == 2) {
			$query = new Query();

			$sql = "DELETE FROM $this->db_addresses WHERE id = ".$action[1];
//			print $sql;
			if($query->sql($sql)) {
				message()->addMessage("Address deleted");
				return true;
			}

		}

		return false;
	}



	// NEWSLETTERS

	// get newsletter info
	// get all newsletters (list of available newsletters)
	// get newsletters for user
	// get state of specific newsletter for specific user
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

			// check for specific newsletter for specific user
			if($newsletter) {
				$sql = "SELECT * FROM ".$this->db_newsletters." WHERE user_id = $user_id AND newsletter = '$newsletter'";
				if($query->sql($sql)) {
					return true;
				}
			}
			// get newsletters for specific user
			else {
				$sql = "SELECT * FROM ".$this->db_newsletters." WHERE user_id = $user_id";
				if($query->sql($sql)) {
					return $query->results();
				}
			}

		}
		// get list of all newsletters
		else {
			$sql = "SELECT newsletter FROM ".$this->db_newsletters." GROUP BY newsletter";
			if($query->sql($sql)) {
				return $query->results();
			}
		}

	}

	function updateNewsletters($action){}



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
	// /janitor/admin/user/saveUserGroup (values in POST)
	function saveUserGroup($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("user_group"))) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_user_groups);
			$query->checkDbExistance($this->db_access);

			$entities = $this->getModel();
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
	// /janitor/admin/user/updateUserGroup/#user_group_id# (values in POST)
	function updateUserGroup($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2) {

			// does values validate
			$entities = $this->getModel();
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
	// /janitor/admin/user/deleteUserGroup/#user_group_id#
	function deleteUserGroup($action) {

		if(count($action) == 2) {

			$query = new Query();
			if($query->sql("DELETE FROM ".$this->db_user_groups." WHERE id = ".$action[1])) {
				message()->addMessage("User group deleted");
				return true;
			}
		}

		message()->addMessage("User group could not be deleted - maybe you still have users in this group?", array("type" => "error"));
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

		// indicate access read state (used when parsing controllers)
		$read_access = true;

		// array to store controller information
		$access = array();
		$access["points"] = array();


		// get and index local controllers
		$controllers = $fs->files(LOCAL_PATH."/www", array("allow_extensions" => "php"));
//		print_r($controllers);

		foreach($controllers as $controller) {
			$access_item = array();

			// get controller access items
			include($controller);

			// replace local path
			$short_point = str_replace(".php", "", str_replace(LOCAL_PATH."/www", "", $controller));
			// store information
			$access["points"][$short_point] = $access_item;
		}


		// get and index framework controllers
		$controllers = $fs->files(FRAMEWORK_PATH."/www", array("allow_extensions" => "php"));
//		print_r($controllers);

		foreach($controllers as $controller) {
			$access_item = array();

			// get controller access items
			include($controller);

			// replace Framework path, but add /janitor/admin because that is reprensentative for how they are accessed
			$short_point = str_replace(".php", "", str_replace(FRAMEWORK_PATH."/www", "/janitor/admin", $controller));

			// store information
			$access["points"][$short_point] = $access_item;
		}


		// get settings for specific user group id
		if($user_group_id) {

			$access["permissions"] = array();

			$query = new Query();
			// make sure type tables exist
			$query->checkDbExistance($this->db_access);

			$sql = "SELECT * FROM ".$this->db_access." WHERE user_group_id = $user_group_id AND permission = 1";
			if($query->sql($sql)) {
				$results = $query->results();
				foreach($results as $result) {

					$access["permissions"][$result["controller"]][$result["action"]] = 1;
				}
			}
		}

//		print_r($access);
		return $access;
	}

	// update user group
	// /updageAccess/#user_group_id#
	// post grants in grants array
	function updateAccess($action) {

		if(count($action) == 2) {

			$query = new Query();

			// get posted grants
			// grants[controller][action] = permission (0/1)
			$grants = getPost("grant");
//			print_r($grants);

			$user_group_id = $action[1];

			// clear cached permissions
			session()->reset("user_group_permissions");

			// make sure type tables exist
			$query->checkDbExistance($this->db_access);

			// remove existing grants
			$query->sql("DELETE FROM ".$this->db_access." WHERE user_group_id = " . $user_group_id);

			// set new grants
			if($grants) {

				// loop through controllers
				foreach($grants as $controller => $actions) {

//					print $controller."<br>\n";

					// loop through actions for controller
					foreach($actions as $access_action => $grant) {

//						print $access_action." = $grant<br>\n";

						if($grant == 1) {
							$sql = "INSERT INTO ".$this->db_access." SET permission = 1, user_group_id = $user_group_id, controller = '$controller', action = '$access_action'";
//							print $sql."<br>";
							$query->sql($sql);
						}
					}
				}
			}


			message()->addMessage("Access grants updated");
			return true;
		}

		message()->addMessage("Access grants could not be updated", array("type" => "error"));
		return false;
	}



	/**
	* Validate username info to avoid too many unneccesary duplet users
	* Look for users with same email and mobile because such combinations indicates same user
	*/
	function matchUsernames($_options) {

		$email = false;
		$mobile = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "email"         : $email          = $_value; break;
					case "mobile"        : $mobile         = $_value; break;

				}
			}
		}

		// user with matching email and mobile
		if($email && $mobile) {

			$email_matches = $this->getUsers(array("email" => $email));
			$mobile_matches = $this->getUsers(array("mobile" => $mobile));

			if($email_matches && $mobile_matches) {
				foreach($email_matches as $user) {
					if(array_search($user, $mobile_matches) !== -1) {
						return $user["user_id"];
					}
				}
			}
		}
		else if($email) {

			$email_matches = $this->getUsers(array("email" => $email));
			if($email_matches) {
				return $email_matches[0]["user_id"];
			}
		}
		else if($mobile) {

			$mobile_matches = $this->getUsers(array("mobile" => $mobile));
			if($mobile_matches) {
				return $mobile_matches[0]["user_id"];
			}
			
		}


		return false;
	}


	/**
	* Validate address info to avoid too many unneccesary duplet addresses
	* Look for addresses with same user_id and label because such combinations indicates same address
	*/
	function matchAddress($_options) {

		$user_id = false;

		$address_label = false;
		$address1 = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "user_id"        : $user_id          = $_value; break;

					case "address_label"  : $address_label    = $_value; break;
					case "address1"       : $address1         = $_value; break;

				}
			}
		}

		$query = new Query();

		// user_id specific
		if($user_id) {

			// look for matching address_label and address1
			if($address_label && $address1) {

				$sql = "SELECT id WHERE user_id = $user_id AND address_label = '$address_label' AND address1 =  '$address1'";
				if($query->sql($sql)) {
					return $query->result(0, "id");
				}
				else {
					return false;
				}
			}
			// matching address_label
			else if($address_label) {
				$sql = "SELECT id WHERE user_id = $user_id AND address_label = '$address_label'";
				if($query->sql($sql)) {
					return $query->result(0, "id");
				}
				else {
					return false;
				}
			}
		}
		
		if(!isset($_options["user_id"])) {

			// look for matching address_label and address1
			if($address_label && $address1) {

				$sql = "SELECT id WHERE address_label = '$address_label' AND address1 =  '$address1'";
				if($query->sql($sql)) {
					return $query->results("id");
				}
				else {
					return false;
				}
			}
			// matching address_label
			else if($address_label) {
				$sql = "SELECT id WHERE address_label = '$address_label'";
				if($query->sql($sql)) {
					return $query->results("id");
				}
				else {
					return false;
				}
			}
			
		}

		return false;
	}

}

?>