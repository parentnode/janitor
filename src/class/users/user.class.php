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
		$this->addToModel("address_label", array(
			"type" => "string",
			"label" => "Address label",
			"hint_message" => "Give this address a label (home, office, parents, etc.)",
			"error_message" => "Invalid label"
		));

		// address label
		$this->addToModel("address_name", array(
			"type" => "string",
			"label" => "Name/Company",
			"hint_message" => "Name on door at address, your name or company name",
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
			"label" => "State/region",
			"hint_message" => "Write your state/region, if applicaple",
			"error_message" => "Invalid state/region"
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


	/**
	* CONTROLLER FUNCTIONS
	*
	*/

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

	// delete user
	// /admin/user/delete/#user_id#
	// TODO: Extend constraint detection
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
					case "mobile"       : $mobile           = $_value; break;
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

		if(count($action) == 2) {

			$user_id = $action[1];
			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_usernames);

			$entities = $this->data_entities;

			$email = $entities["email"]["value"];
			$mobile = $entities["mobile"]["value"];

			$current_email = $this->getUsernames(array("user_id" => $user_id, "type" => "email"));
			$current_mobile = $this->getUsernames(array("user_id" => $user_id, "type" => "mobile"));

			// email does not exist
			if(!$current_email) {
				$sql = "INSERT INTO $this->db_usernames SET username = '$email', verified = 0, type = 'email', user_id = $user_id";
				if($query->sql($sql)) {
					message()->addMessage("Email added");
				}
				else {
					message()->addMessage("Could not add email", array("type" => "error"));
				}
			}
			// is email changed?
			else if($email != $current_email) {

				$sql = "UPDATE $this->db_usernames SET username = '$email', verified = 0 WHERE type = 'email' AND user_id = $user_id";
				print $sql;
				if($query->sql($sql)) {
					message()->addMessage("Email updated");
				}
				else {
					message()->addMessage("Could not update email", array("type" => "error"));
				}
			}

			// mobile does not exist
			if(!$current_email) {
				$sql = "INSERT INTO $this->db_usernames SET username = '$mobile', verified = 0, type = 'mobile', user_id = $user_id";
				if($query->sql($sql)) {
					message()->addMessage("Mobile added");
				}
				else {
					message()->addMessage("Could not add mobile", array("type" => "error"));
				}
			}
			// is mobile changed?
			if($mobile != $current_mobile) {

				$sql = "UPDATE $this->db_usernames SET username = '$mobile', verified = 0 WHERE type = 'mobile' AND user_id = $user_id";
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

	// NOT NEEDED YET
	function deleteUsername() {}



	// check if password exists
	function issetPassword($user_id) {
		//
	}

	// set new password for user
	// user/setPassword/#user_id#
	function setPassword($action) {

		if(count($action) == 2) {

			// does values validate
			if($this->validateList(array("password"))) {

				$user_id = $action[1];
				$query = new Query();

				// make sure type tables exist
				$query->checkDbExistance($this->db_passwords);


				$entities = $this->data_entities;

				$password = sha1($entities["password"]["value"]);
				$sql = "INSERT INTO ".$this->db_passwords." SET user_id = $user_id, password = '$password'";
				if($query->sql($sql)) {
					message()->addMessage("password saved");
					return true;
				}
			}
		}

		message()->addMessage("Password could not be saved", array("type" => "error"));
		return false;
	}


	// start reset password procedure
	function resetPassword() {}


	// return addresses
	// can return all addresses for a user, or a specific address
	// TODO: translate country ISO to country text
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

			$sql = "SELECT * FROM ".$this->db_addresses." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				return $query->results();
			}

		}
		else if($address_id) {
			$sql = "SELECT * FROM ".$this->db_addresses." WHERE address_id = $address_id";
			if($query->sql($sql)) {
				return $query->result(0);
			}
		}
		
		
	}

	// create a new address
	function addAddress($action) {
		if(count($action) == 2) {
			$query = new Query();

			// does values validate
			if($this->validateList(array("address_label","address_name","address_att","address1","postal","city","state","country"))) {

				$query = new Query();

				// TODO: get values from entities instead of post

				$entities = getPosts(array("user_id","address_label","address_name","att","address1","address2","city","postal","state","country"));
				$names = array();
				$values = array();

				foreach($entities as $name => $entity) {
//					if($entity["value"] && preg_match("/^()$/", $name)) {
						$names[] = $name;
						$values[] = $name."='".$entity."'";
//					}
				}

				if($values) {
					$sql = "INSERT INTO ".$this->db_addresses." SET ".implode(",", $values);
//					print $sql;
				}

				if(!$values || $query->sql($sql)) {
					return array("address_id" => $query->lastInsertId());
//					return true;
				}
			}
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

		// indicate access read state (used when parsing controllers)
		$read_access = true;

		$access = array();
		$access["points"] = array();

		// local controllers
		$controllers = $fs->files(LOCAL_PATH."/www", array("allow_extensions" => "php"));
//		print_r($controllers);

		foreach($controllers as $controller) {
			$access_item = array();

			include($controller);
//			if($access_item) {
//				print_r($access_item);

			// replace local path
			$short_point = str_replace(".php", "", str_replace(LOCAL_PATH."/www", "", $controller));
			// remove index path, because it is not being used in requests
			$short_point = preg_replace("/\/index$/", "", $short_point);

			$access["points"][$short_point] = array();
	
			if($access_item) {
				// access restriction on any type of request
				foreach($access_item as $action => $restricted) {
					if($restricted === true) {
						$access["points"][$short_point][] = $action;
					}
				}
			}
		}


		// framework controllers
		$controllers = $fs->files(FRAMEWORK_PATH."/www", array("allow_extensions" => "php"));
//		print_r($controllers);
		foreach($controllers as $controller) {
			$access_item = array();

//			print $controller."<br>";

			// replace Framework path, but add Admin because that is reprensentative for how they are accessed
			$short_point = str_replace(".php", "", str_replace(FRAMEWORK_PATH."/www", "/admin", $controller));

			// TODO: Check if controller is enabled via Apache Alias (don't know how - find a way)
			// maybe be requesting file with http://domain/controller

			$http_request_url = (isset($_SERVER["HTTPS"]) ? "https" : "http") . "://" . $_SERVER["SERVER_NAME"] . $short_point;
//			print $http_request_url . "<br>";
			

			$file_headers = get_headers($http_request_url);
//			print_r($file_headers);
			if(!preg_match("/404/", $file_headers[0])) {
				include($controller);
//				print_r($access_item);

				// remove index path, because it is not being used in requests
				$short_point = preg_replace("/\/index$/", "", $short_point);

				$access["points"][$short_point] = array();

				if($access_item) {
					// access restriction on any type of request
					foreach($access_item as $action => $restricted) {
						if($restricted === true) {
							$access["points"][$short_point][] = $action;
						}
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


			if($query->sql("SELECT * FROM ".$this->db_access." WHERE user_group_id=$user_group_id AND permission=1")) {
				$results = $query->results();
				foreach($results as $result) {
					$access["permissions"][$result["action"]] = 1;
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
			$grants = getPost("grant");
			$user_group_id = $action[1];

//			print_r($grants);

			// remove existing grants
			$query->sql("DELETE FROM ".$this->db_access." WHERE user_group_id = " . $user_group_id);

			$create_count = 0;
			// set new grants
			if($grants) {
				foreach($grants as $path => $grant) {
					if($grant == 1) {
						$sql = "INSERT INTO ".$this->db_access." SET permission=1, user_group_id = $user_group_id, action = '$path'";
//						print $sql."<br>";
						if($query->sql($sql)) {
							$create_count++;
						}
					}
					else {
						$sql = "INSERT INTO ".$this->db_access." SET permission=0, user_group_id=$user_group_id, action = '$path'";
//						print $sql."<br>";
						if($query->sql($sql)) {
							$create_count++;
						}
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