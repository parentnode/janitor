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
					global $page;
					$page->addLog("User created (" . $query->lastInsertId() . ") by (" . session()->value("user_id") . ")");

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
					$sql = "UPDATE ".$this->db." SET ".implode(",", $values).",modified_at=CURRENT_TIMESTAMP WHERE id = ".$user_id;
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

		$user_id = $action[1];
		$status = $action[2];

		if(count($action) == 3 && isset($status_states[$status])) {
		
			$query = new Query();

			// update status for user
			if($query->sql("SELECT id FROM ".$this->db." WHERE id = ".$user_id)) {
				$query->sql("UPDATE ".$this->db." SET status = ".$status." WHERE id = ".$user_id);

				// flush user session if user is disabled
				if($status == 0) {
					$this->flushUserSession(array("flushUserSession", $user_id));
				}

				message()->addMessage("User ".$status_states[$status]);
				return true;
			}
			message()->addMessage("User could not be ".$status_states[$status], array("type" => "error"));

		}
		return false;

	}

	// delete user
	// /janitor/admin/user/delete/#user_id#
	// TODO: Extend constraint detection
	function delete($action) {

		if(count($action) == 2) {
			$query = new Query();
			$user_id = $action[1];
			
			$sql = "DELETE FROM $this->db WHERE id = ".$user_id;
//			print $sql;
			if($query->sql($sql)) {
				
				// flush user session when user is deleted
				$this->flushUserSession(array("flushUserSession", $user_id));

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


	// flush a user session from Memcached sessions
	// /janitor/admin/user/flushUserSession/#user_id#
	function flushUserSession($action) {

		if(count($action) == 2) {
			$user_id = $action[1];

			if(class_exists("Memcached")) {

				$memc = new Memcached();
				$memc->addServer('localhost', 11211);

				$keys = $memc->getAllKeys();

				foreach($keys as $key) {

					if(preg_match("/sess\.key/", $key)) {
						$user = $memc->get($key);
			//			print "session:" . $user."<br>\n";

						if($user) {

							$data = cache()->unserializeSession($user);

							if(isset($data["SV"]) && $data["SV"]["site"] == SITE_URL && $data["SV"]["user_id"] == $user_id) {
								$memc->delete($key);

								message()->addMessage("User session flushed");
								return true;
							}
						}
					}
				}
			}
		}

		message()->addMessage("No user session found", array("type" => "error"));
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
			// Exclude Guest user for all-users list
			if($query->sql("SELECT * FROM ".$this->db." WHERE id != 1 ORDER BY $order")) {
				 return $query->results();
			}
		}

		return false;
	}




	// USERNAMES
	// usernames are thought to allow multiple emails or mobile numbers, but for now
	// they are restricted to just one of each
	//
	// At later point interface and functionality should be expanded to intended level


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
	// /janitor/admin/user/updateEmail/#user_id#
	function updateEmail($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does action match expected
		if(count($action) == 2) {

			$user_id = $action[1];
			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_usernames);

			$email = $this->getProperty("email", "value");

			// check if email exists
			if($this->userExists(array("email" => $email, "user_id" => $user_id))) {
				message()->addMessage("Email already exists", array("type" => "error"));
				return false;
			}


			$current_email = $this->getUsernames(array("user_id" => $user_id, "type" => "email"));

			// email is sent
			if($email) {

				// email has not been set before
				if(!$current_email) {

					$sql = "INSERT INTO $this->db_usernames SET username = '$email', verified = 0, type = 'email', user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
						message()->addMessage("Email added");
						return true;
					}
				}

				// email is changed
				else if($email != $current_email) {

					$sql = "UPDATE $this->db_usernames SET username = '$email', verified = 0 WHERE type = 'email' AND user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
						message()->addMessage("Email updated");
						return true;
					}
				}

				// email is NOT changed
				else if($email == $current_email) {

					message()->addMessage("Email unchanged");
					return true;
				}
			}

			// email is not sent
			else if(!$email && $current_email !== false) {

				$sql = "DELETE FROM $this->db_usernames WHERE type = 'email' AND user_id = $user_id";
//				print $sql."<br>";
				if($query->sql($sql)) {
					message()->addMessage("Email deleted");
					return true;
				}
			}

		}

		message()->addMessage("Could not update email", array("type" => "error"));
		return false;

	}

	// Update usernames from posted values
	// /janitor/admin/user/updateMobile/#user_id#
	function updateMobile($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does action match expected
		if(count($action) == 2) {

			$user_id = $action[1];
			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_usernames);

			$mobile = $this->getProperty("mobile", "value");

			// check if mobile exists
			if($this->userExists(array("mobile" => $mobile, "user_id" => $user_id))) {
				message()->addMessage("Mobile already exists", array("type" => "error"));
				return false;
			}


			$current_mobile = $this->getUsernames(array("user_id" => $user_id, "type" => "mobile"));

			// mobile is sent
			if($mobile) {

				// mobile has not been set before
				if(!$current_mobile) {

					$sql = "INSERT INTO $this->db_usernames SET username = '$mobile', verified = 0, type = 'mobile', user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
						message()->addMessage("Mobile added");
						return true;
					}
				}

				// mobile is changed
				else if($mobile != $current_mobile) {

					$sql = "UPDATE $this->db_usernames SET username = '$mobile', verified = 0 WHERE type = 'mobile' AND user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
						message()->addMessage("Mobile updated");
						return true;
					}
				}

				// mobile is NOT changed
				else if($mobile == $current_mobile) {

					message()->addMessage("Mobile unchanged");
					return true;
				}
			}

			// mobile is not sent
			else if(!$mobile && $current_mobile !== false) {

				$sql = "DELETE FROM $this->db_usernames WHERE type = 'mobile' AND user_id = $user_id";
//				print $sql."<br>";
				if($query->sql($sql)) {
					message()->addMessage("Mobile deleted");
					return true;
				}
			}

		}

		message()->addMessage("Could not update mobile", array("type" => "error"));
		return false;

	}




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




	// API TOKEN

	// get users api token
	function getToken($user_id = false) {

		$query = new Query();
		// make sure type tables exist
		$query->checkDbExistance($this->db_apitokens);

		$sql = "SELECT token FROM ".$this->db_apitokens." WHERE user_id = $user_id";
		if($query->sql($sql)) {
			return $query->result(0, "token");
		}
		return false;
	}

	// create new api token
	// user/renewToken/#user_id#
	function renewToken($action) {

		$user_id = $action[1];

		$token = gen_uuid();
		$query = new Query();

		// make sure type tables exist
		$query->checkDbExistance($this->db_apitokens);

		$sql = "SELECT token FROM ".$this->db_apitokens." WHERE user_id = $user_id";
		if($query->sql($sql)) {
			$sql = "UPDATE ".$this->db_apitokens." SET token = '$token' WHERE user_id = $user_id";
		}
		else {
			$sql = "INSERT INTO ".$this->db_apitokens." SET user_id = $user_id, token = '$token'";
		}
		if($query->sql($sql)) {
			return $token;
		}

		return false;
	}

	// disable api token
	// /janitor/admin/profile/disableToken
	function disableToken($action) {


		$user_id = $action[1];

		$query = new Query();

		// make sure type tables exist
		$query->checkDbExistance($this->db_apitokens);

		$sql = "DELETE FROM ".$this->db_apitokens." WHERE user_id = $user_id";
//		print $sql;
		if($query->sql($sql)) {
			return true;
		}

		return false;
	}



	// ADDRESSES

	// return addresses
	// can return all addresses for a user, or a specific address
	// Adds country_name for stored country ISO value
	function getAddresses($_options = false) {

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
				$sql = "INSERT INTO ".$this->db_addresses." SET user_id=$user_id," . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
					message()->addMessage("Address created");
					return array("item_id" => $user_id);
				}
			}
		}

		message()->addMessage("Address could not be saved", array("type" => "error"));
		return false;
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
				$sql = "UPDATE ".$this->db_addresses." SET ".implode(",", $values).",modified_at=CURRENT_TIMESTAMP WHERE id = ".$address_id;
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
	function getNewsletters($_options = false) {

		$user_id = false;
		$newsletter = false;
		$newsletter_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"           : $user_id             = $_value; break;
					case "newsletter"        : $newsletter          = $_value; break;
					case "newsletter_id"     : $newsletter_id       = $_value; break;
				}
			}
		}

		$query = new Query();

		if($user_id) {

			// check for specific newsletter (by nane) for specific user
			if($newsletter) {
				$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.newsletter_id, newsletters.name FROM ".$this->db_newsletters." as subscribers, ".UT_NEWSLETTERS." as newsletters WHERE subscribers.user_id = $user_id AND subscribers.newsletter_id = newsletters.id AND newsletters.newsletter = '$newsletter'";
				if($query->sql($sql)) {
					return $query->result(0);
				}
			}
			// check for specific newsletter (by id) for specific user
			else if($newsletter_id) {
				$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.newsletter_id, newsletters.name FROM ".$this->db_newsletters." as subscribers, ".UT_NEWSLETTERS." as newsletters WHERE subscribers.user_id = $user_id AND subscribers.newsletter_id = '$newsletter_id'";
				if($query->sql($sql)) {
					return $query->result(0);
				}
			}
			// get newsletters for specific user
			else {
				$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.newsletter_id, newsletters.name FROM ".$this->db_newsletters." as subscribers, ".UT_NEWSLETTERS." as newsletters WHERE subscribers.user_id = $user_id AND subscribers.newsletter_id = newsletters.id";
				if($query->sql($sql)) {
					return $query->results();
				}
			}

		}
		// get list of all newsletter subscribers
		else {
			$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.newsletter_id, newsletters.name FROM ".$this->db_newsletters." as subscribers, ".UT_NEWSLETTERS." as newsletters";
			if($query->sql($sql)) {
				return $query->results();
			}
		}

	}


	// /janitor/admin/user/addNewsletter/#user_id#
	// Newsletter info i $_POST
	function addNewsletter($action){

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 2 && $this->validateList(array("newsletter_id"))) {

			$query = new Query();
			$user_id = $action[1];

			$newsletter_id = $this->getProperty("newsletter_id", "value");

			// already signed up (to avoid faulty double entries)
			$sql = "SELECT id FROM $this->db_newsletters WHERE user_id = $user_id AND newsletter_id = '$newsletter_id'";
			if(!$query->sql($sql)) {
				$sql = "INSERT INTO ".$this->db_newsletters." SET user_id=$user_id, newsletter_id='$newsletter_id'";
				$query->sql($sql);
			}

			message()->addMessage("Subscribed to newsletter");
			return true;
		}

		message()->addMessage("Could not subscribe to newsletter", array("type" => "error"));
		return false;
	}

	// /janitor/admin/user/deleteNewsletter/#user_id#/#newsletter_id#
	function deleteNewsletter($action){

		// does values validate
		if(count($action) == 3) {

			$query = new Query();
			$user_id = $action[1];
			$newsletter_id = $action[2];

			$sql = "DELETE FROM $this->db_newsletters WHERE user_id = $user_id AND newsletter_id = '$newsletter_id'";
			if($query->sql($sql)) {
				message()->addMessage("Unsubscribed from newsletter");
				return true;
			}
		}

		message()->addMessage("Could not unsubscribe from newsletter", array("type" => "error"));
		return false;

	}



	// READSTATES
	function getReadstates($_options = false) {

	}


	// SUBSCRIPTIONS
	function getSubscriptions($_options = false) {
		$IC = new Items();
		global $page;

		$user_id = false;
		$item_id = false;
		$subscription_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"             : $user_id               = $_value; break;
					case "item_id"             : $item_id               = $_value; break;
					case "subscription_id"     : $subscription_id       = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific subscription
		if($subscription_id) {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE id = $subscription_id";
			if($query->sql($sql)) {
				$subscription = $query->result(0);

				// extend payment method details
				if($subscription["payment_method"]) {
					$payment_method = $subscription["payment_method"];
					$subscription["payment_method"] = $page->paymentMethods($payment_method);
				}

				// get subscription item
				$subscription["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));

				// add membership info if user_id is available
				if($user_id) {
					// is this subscription used for membership
					$subscription["membership"] = ($this->getMembers(array("item_id" => $subscription["item_id"], "user_id" => $user_id)) ? true : false);
				}

				return $subscription;
			}

		}

		// get subscription for specific user
		else if($user_id) {

			// check for specific subscription for specific user
			if($item_id) {
				$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id AND item_id = '$item_id' LIMIT 1";
				if($query->sql($sql)) {
					$subscription = $query->result(0);

					// extend payment method details
					if($subscription["payment_method"]) {
						$payment_method = $subscription["payment_method"];
						$subscription["payment_method"] = $page->paymentMethods($payment_method);
					}

					// get subscription item
					$subscription["item"] = $IC->getItem(array("id" => $item_id, "extend" => array("prices" => true, "subscription_method" => true)));

					// is this subscription used for membership
					$subscription["membership"] = ($this->getMembers(array("item_id" => $item_id, "user_id" => $user_id)) ? true : false);

					return $subscription;
				}
			}
			// get all subscriptions for specific user
			else {
				$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id";
				if($query->sql($sql)) {

					$subscriptions = $query->results();
					foreach($subscriptions as $i => $subscription) {

						// extend payment method details
						if($subscription["payment_method"]) {
							$payment_method = $subscription["payment_method"];
							$subscriptions[$i]["payment_method"] = $page->paymentMethods($payment_method);
						}

						// get subscription item
						$subscriptions[$i]["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));

						// is this subscription used for membership
						$subscriptions[$i]["membership"] = ($this->getMembers(array("item_id" => $subscription["item_id"], "user_id" => $user_id)) ? true : false);
					}
					return $subscriptions;
				}
			}

		}


		// TODO
		// get all subscribers to specific item
		else if($item_id) {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE item_id = '$item_id'";
			if($query->sql($sql)) {
				$subscribers = $query->results();
				foreach($subscribers as $i => $subscriber) {
					$subscribers[$i]["user"] = $this->getUsers(array("user_id" => $subscriber["user_id"]));
					$subscribers[$i]["membership"] = ($this->getMembers(array("item_id" => $subscription["item_id"], "user_id" => $subscriber["user_id"])) ? true : false);
				}
				return $subscribers;
			}
		}

		// get list of all subscriptions
		// TODO: for list all
		else {
			$sql = "SELECT * FROM ".$this->db_subscriptions." GROUP BY item_id";
			if($query->sql($sql)) {
				$subscriptions = $query->results();
				foreach($subscriptions as $i => $subscription) {
					$subscriptions[$i] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => true));
				}
				return $subscriptions;
			}
		}
		return false;
	}

	// /janitor/admin/user/addSubscription
	// info i $_POST
	function addSubscription($action) {
		
		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("item_id", "user_id"))) {

			$query = new Query();
			$IC = new Items();

			$item_id = $this->getProperty("item_id", "value");
			$user_id = $this->getProperty("user_id", "value");
			$payment_method = $this->getProperty("payment_method", "value");

			// get item prices and subscription method details to create subscription correctly
			$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true, "prices" => true)));

			// item has price
			if($item && $item["prices"]) {

				// require payment method
				if($payment_method) {

					$sql = "INSERT INTO ".$this->db_subscriptions." SET user_id = $user_id, item_id = $item_id, payment_method = $payment_method";

					// does subscription expire
					if($item["subscription_method"]) {

						// add expires_at date
//						$sql .= ", "; 

					}

					// create order for payment

				}
				
			}
			// item does not have price
			else {
				$sql = "INSERT INTO ".$this->db_subscriptions." SET user_id = $user_id, item_id = $item_id";
			}

			// print $sql;
			if($sql && $query->sql($sql)) {
				message()->addMessage("Subscription added");
				return true;
			}
		}

		message()->addMessage("Subscription could not be added", array("type" => "error"));
		return false;
	}

	// /janitor/admin/user/updateSubscription/#user_id#/#subscription_id#
	// info i $_POST
	function updateSubscription($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 3) {
			$user_id = $action[1];
			$subscription_id = $action[2];


			$query = new Query();
			$values = "modified_at=CURRENT_TIMESTAMP";

			$payment_method = $this->getProperty("payment_method", "value");
			if($payment_method) {
				$values .= ", payment_method=$payment_method";
			}

			// new expires_at date was sent
			$expires_at = getPost("expires_at");
			if($expires_at) {
				$values .= ", expires_at=".date("Y-m-d H:i:s", strtotime($expires_at));
			}
			// new renewed_at date was sent
			$renewed_at = getPost("renewed_at");
			if($renewed_at) {
				$values .= ", renewed_at=".date("Y-m-d H:i:s", strtotime($renewed_at));
			}

			$sql = "UPDATE ".$this->db_subscriptions. " SET $values WHERE id = $subscription_id AND user_id = $user_id";
			// print $sql;
			if($query->sql($sql)) {
				message()->addMessage("Subscription updated");
				return true;				
			}

		}

		message()->addMessage("Subscription could not be updated", array("type" => "error"));
		return false;
	}


	// /janitor/admin/user/deleteSubscription/#user_id#/#subscription_id#
	function deleteSubscription($action) {

		// does values validate
		if(count($action) == 3) {
			$user_id = $action[1];
			$subscription_id = $action[2];

			$query = new Query();

			// check membership dependency
			$sql = "SELECT id FROM ".$this->db_members." WHERE subscription_id = $subscription_id";
			if(!$query->sql($sql)) {

				$sql = "DELETE FROM ".$this->db_subscriptions." WHERE id = $subscription_id AND user_id = $user_id";
				if($query->sql($sql)) {
					message()->addMessage("Subscription deleted");
					return true;				
				}
			}
		}

		message()->addMessage("Subscription could not be deleted", array("type" => "error"));
		return false;
	}




	// MEMBERS

	function getMembers($_options = false) {
		$IC = new Items();

		$user_id = false;
		$item_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"     : $user_id       = $_value; break;
					case "item_id"     : $item_id       = $_value; break;
				}
			}
		}

		$query = new Query();

		if($user_id) {

			// get specific membership for specific user
			if($item_id) {
				// check if
//				$sql = "SELECT * FROM ".$this->db_subscriptions." as sub, ".$this->db_members." as mem WHERE mem.user_id = $user_id AND mem.subscription_id = sub.id";

				$sql = "SELECT * FROM ".$this->db_subscriptions." as sub, ".$this->db_members." as mem WHERE mem.user_id = $user_id AND sub.item_id = '$item_id' AND mem.subscription_id = sub.id LIMIT 1";
				if($query->sql($sql)) {
					$subscription = $query->result(0);
					$item = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("subscription_method" => true, "prices" => true)));

					return $item;
				}
			}
			// get all memberships for specific user
			else {
				$sql = "SELECT * FROM ".$this->db_subscriptions." as sub, ".$this->db_members." as mem WHERE mem.user_id = $user_id AND mem.subscription_id = sub.id";
				if($query->sql($sql)) {

					$members = $query->results();
					foreach($members as $i => $member) {
						$members[$i] = $IC->getItem(array("id" => $member["item_id"], "extend" => array("subscription_method" => true, "prices" => true)));
					}
					return $members;
				}
			}

		}
		// get all members with specific membership
		else if($item_id) {
			$sql = "SELECT * FROM ".$this->db_members." WHERE item_id = '$item_id'";
//			print $sql;
			if($query->sql($sql)) {
				$members = $query->results();
				foreach($members as $i => $member) {
					print_r($member);
					$members[$i] = $this->getUsers(array("user_id" => $member["user_id"]));
					$members[$i]["subscription_id"] = $member["id"];
					print_r($members[$i]);
				}
				return $members;
			}
		}

		// get list of all members
		else {
			$sql = "SELECT * FROM ".$this->db_members;
//			print $sql;
			if($query->sql($sql)) {
				$members = $query->results();
				foreach($members as $i => $member) {
					$members[$i][""] = $IC->getItem(array("id" => $member["item_id"], "extend" => true));
				}
				return $members;
			}
		}

		return false;
	}


	// /janitor/admin/user/addMember
	// info i $_POST
	function addMember($action) {
		
		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("membership_id", "user_id"))) {

			$query = new Query();

			$membership_id = $this->getProperty("membership_id", "value");
			$user_id = $this->getProperty("user_id", "value");

			// already signed up (to avoid faulty double entries)
			$sql = "SELECT id FROM $this->db_members WHERE user_id = $user_id AND subscription_id = '$membership_id'";
			if(!$query->sql($sql)) {
				$sql = "INSERT INTO ".$this->db_members." SET user_id=$user_id, subscription_id='$membership_id'";
				$query->sql($sql);
			}

			message()->addMessage("Member added");
			return true;
		}

		message()->addMessage("Could not add member", array("type" => "error"));
		return false;
	}

	function deleteMember($action) {}




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
			//session()->reset("user_group_permissions");
			cache()->reset("user_group_".$user_group_id."_permissions");

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