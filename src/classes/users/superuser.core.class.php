<?php
/**
* @package janitor.users
* This file contains Admin User functionality
*/

class SuperUserCore extends User {

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
			$query->checkDbExistence($this->db);

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

					$user_id = $query->lastInsertId();

					// itemtype post save handler?
					// TODO: Consider if failed postSave should have consequences
					if(method_exists($this, "saved")) {
						$this->saved($user_id);
					}

					global $page;
					$page->addLog("User created: user_id:" . $user_id . ", created by: " . session()->value("user_id"));

					message()->addMessage("User created");
					return array("item_id" => $user_id);
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

			// -2 => "deleted",
			// 0 => "new",

			-1 => "cancelled",
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

	// cancel user
	// accounts with unpaid orders cannot be cancelled
	// /janitor/admin/user/cancel/#user_id#
	function cancel($action) {

		if(count($action) == 2) {
			$query = new Query();
			$user_id = $action[1];
			global $page;

			$user = $this->getUsers(["user_id" => $user_id]);
			// do not attempt to get cancelled or non-existent users
			if($user && $user["status"] >= 0) {
			// check for unpaid orders
				$unpaid_orders = false;
				if(defined("SITE_SHOP") && SITE_SHOP) {
					include_once("classes/shop/supershop.class.php");
					$SC = new SuperShop();
					$unpaid_orders = $SC->getUnpaidOrders(["user_id" => $user_id]);
				}

				// do not allow to cancel users with unpaid orders
				if(!$unpaid_orders) {

					// WHEN UPDATING - ALSO UPDATE USER CORE VERSION
					// Update name to "Anonymous" and remove all privileges
					$sql = "UPDATE ".$this->db." SET status=-1,user_group_id=NULL,nickname='Anonymous',firstname='',lastname='',language=NULL,modified_at=CURRENT_TIMESTAMP WHERE id = ".$user_id;
					if($query->sql($sql)) {


						// delete usernames
						$sql = "DELETE FROM ".$this->db_usernames." WHERE user_id = ".$user_id;
						$query->sql($sql);

						// delete activation reminders
						$sql = "DELETE FROM ".SITE_DB.".user_log_activation_reminders WHERE user_id = ".$user_id;
						$query->sql($sql);

						// delete password
						$sql = "DELETE FROM ".$this->db_passwords." WHERE user_id = ".$user_id;
						$query->sql($sql);
						// delete password reset tokens
						$sql = "DELETE FROM ".$this->db_password_reset_tokens." WHERE user_id = ".$user_id;
						$query->sql($sql);

						// delete activation reminders
						$sql = "DELETE FROM ".SITE_DB.".user_log_verification_links WHERE user_id = ".$user_id;
						$query->sql($sql);

						// delete api tokens
						$sql = "DELETE FROM ".$this->db_apitokens." WHERE user_id = ".$user_id;
						$query->sql($sql);

						// delete maillists
						$sql = "DELETE FROM ".$this->db_maillists." WHERE user_id = ".$user_id;
						$query->sql($sql);

						// delete readstates
						$sql = "DELETE FROM ".$this->db_readstates." WHERE user_id = ".$user_id;
						$query->sql($sql);

						// delete membership
						if(defined("SITE_MEMBERS") && SITE_MEMBERS) {
							$sql = "DELETE FROM ".$this->db_members." WHERE user_id = ".$user_id;
							$query->sql($sql);
						}

						// delete subscriptions
						if(defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS) {
							$sql = "DELETE FROM ".$this->db_subscriptions." WHERE user_id = ".$user_id;
							$query->sql($sql);
						}


						// delete carts
						if(defined("SITE_SHOP") && SITE_SHOP) {
							$sql = "DELETE FROM ".$SC->db_carts." WHERE user_id = ".$user_id;
							$query->sql($sql);


							// we should also delete user account at payment gateway
							payments()->deleteGatewayUserId($user_id);

							// // TODO: keep updated when more gateways are added
							// include_once("classes/adapters/stripe.class.php");
							// $GC = new JanitorStripe();
							// $payment_methods = $page->paymentMethods();
							//
							// foreach($payment_methods as $payment_method) {
							//
							// 	if($payment_method["gateway"] == "stripe") {
							//
							// 		$GC->deleteCustomer($user_id);
							//
							// 	}
							//
							// }

						}

						// flush user session when user is deleted
						$this->flushUserSession(array("flushUserSession", $user_id));


						// add to log
						global $page;
						$page->addLog("SuperUser->cancel: user_id:$user_id");


						message()->addMessage("Account cancelled");
						return true;

					}

				}
				else if($unpaid_orders){
					message()->addMessage("Unpaid orders exists", array("type" => "error"));
					return array("error" => "unpaid_orders");
				}
			}
			else {
				message()->addMessage("User does not exist.", array("type" => "error"));
				return false;	
			}
			
		}

		message()->addMessage("Cancelling user failed", array("type" => "error"));
		return false;
	}

	// delete user
	// /janitor/admin/user/delete/#user_id#
	// TODO: Extend constraint detection
	function delete($action) {

		if(count($action) == 2) {
			$IC = new Items();
			$query = new Query();
			$user_id = $action[1];

			$orders = false;
			$payments = false;
			if(defined("SITE_SHOP") && SITE_SHOP) {
				include_once("classes/shop/supershop.class.php");
				$SC = new SuperShop();
				$orders = $SC->getOrders(["user_id" => $user_id]);
				$payments = $SC->getpayments(["user_id" => $user_id]);
			}

			$membership = false;
			if(defined("SITE_MEMBERS") && SITE_MEMBERS) {
				$membership = $this->getMembers(["user_id" => $user_id]);
			}


			$items = $IC->getItems(["user_id" => $user_id]);
			$comments = $IC->getComments(["user_id" => $user_id]);


			if(!$orders && !$payments && !$items && !$comments && !$membership) {

				$sql = "DELETE FROM $this->db WHERE id = ".$user_id;
//				print $sql;
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
			else {

				message()->addMessage("User membership, orders, payments, comments or items prevent it from being deleted.", array("type" => "error"));
				return false;

			}

		}

		message()->addMessage("Deleting user failed", array("type" => "error"));
		return false;
	}

	// flush a user session from Redis/Memcached sessions
	// /janitor/admin/user/flushUserSession/#user_id#
	function flushUserSession($action) {

		if(count($action) == 2) {
			$user_id = $action[1];

			$online_users = cache()->getAllDomainSessions();
			if($online_users) {

				foreach($online_users as $user) {

					if($user["user_id"] == $user_id) {
						cache()->reset($user["session_key"]);

						message()->addMessage("User session flushed");
						return true;
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
	* Get user associated with specific username_id
	* Get users with email as username
	* Get users with mobile as username
	*/
	function getUsers($_options=false) {

		// default values
		$user_id = false;
		$user_group_id = false;
		$order = "status DESC, id DESC";

		$username_id = false;
		$email = false;
		$mobile = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "user_group_id"	: $user_group_id	= $_value; break;
					case "user_id"			: $user_id			= $_value; break;
					case "order"			: $order			= $_value; break;

					case "username_id"		: $username_id		= $_value; break;	
					case "email"			: $email			= $_value; break;
					case "mobile"			: $mobile			= $_value; break;
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

		// get user associated with specific username_id
		else if($username_id) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE id = $username_id";
//			print $sql;
			if($query->sql($sql)) {
				$user = $query->result(0);
				return $user;			}
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
		else if(!isset($_options["user_id"]) && !isset($_options["user_group_id"]) && !isset($_options["email"]) && !isset($_options["mobile"])) {
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


	
	/**
	 * Get usernames or specific username
	 *
	 * @param array $_options Filtering options
	 * 		username_id 	int			Returns specific username
	 * 		user_id 		int			 
	 * 		type 			string		"email"|"mobile"	Requires user_id. Returns first username of type for user_id.
	 * 
	 * @return array|false query result (columns: id, user_id, username, type, verified, verification_code), query results, or false
	 */
	function getUsernames($_options) {

		$username_id = false;
		$user_id = false;
		$type = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "username_id"	: $username_id 	= $_value; break;
					case "user_id"  	: $user_id		= $_value; break;
					case "type"     	: $type			= $_value; break;
				}
			}
		}

		$query = new Query();

		if($username_id) {
			$sql = "SELECT * FROM ".$this->db_usernames." WHERE id = $username_id";
			if($query->sql($sql)) {
				return $query->result(0);
			}
		}

		else if($user_id) {

			// return first username of type
			if($type) {
				$sql = "SELECT * FROM ".$this->db_usernames." WHERE user_id = $user_id AND type = '$type'";
				if($query->sql($sql)) {
					return $query->result(0);
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

	// get usernames or specific username
	function getVerificationCode($type, $username) {

		$query = new Query();
		if($type && $username) {

			$sql = "SELECT id, verification_code FROM ".$this->db_usernames." WHERE username = '$username' AND type = '$type'";
			if($query->sql($sql)) {

				$verification_code = $query->result(0, "verification_code");
				if(!$verification_code) {

					$id = $query->result(0, "id");
					$verification_code = randomKey(8);

					$sql = "UPDATE $this->db_usernames SET verification_code = '$verification_code' WHERE id = $id";
					$query->sql($sql);

				}

				return $verification_code;
			}

		}

		return false;
	}

	/**
	 * Get verification status for username
	 *
	 * @param integer $username_id
	 * @param integer $user_id
	 * @return array|false Array with verification status and number of verification links that have been send. False on error.
	 */
	function getVerificationStatus($username_id, $user_id) {
		$query = new Query();

		$verification_status = [];

		$sql = "SELECT verified FROM ".$this->db_usernames." WHERE user_id = '$user_id' AND id = '$username_id'";	
		// print $sql;
		if($query->sql($sql)) {
			$verification_status["verified"] = $query->result(0, "verified");
			$verification_status["total_reminders"] = false;
			$verification_status["reminded_at"] = false;


			$sql = "SELECT id, reminded_at FROM ".SITE_DB.".user_log_verification_links WHERE user_id = '$user_id' AND username_id = '$username_id'";

			if($query->sql($sql)) {
				$result = $query->results();	
				
				// count the number of verification links that has been send to the user
				$verification_status["total_reminders"] = count($result);
				
				// find out when the latest reminder was sent
				$verification_status["reminded_at"] = end($result)["reminded_at"];
			}
			else {
				$verification_status["total_reminders"] = 0;
			}
			
			return $verification_status;
		
		}

		// error
		return false;


	}
	
	/**
	 * Set verification status for username
	 *
	 * @param integer $username_id
	 * @param integer $user_id
	 * @param integer $verification_status (1 or 0)
	 * 
	 * @return array|false status code or false
	 */
	function setVerificationStatus($username_id, $user_id, $verification_status) {

		$query = new Query();

		if($verification_status == 1) {

			// verify
			$sql = "UPDATE ".$this->db_usernames." SET verified = 1 WHERE user_id = '$user_id' AND id = '$username_id'";
			if($query->sql($sql)) {
				message()->addMessage("Email verified");
				return array("verification_status" => "VERIFIED");
			}
		}

		else if($verification_status == 0) {
		
			// unverify
			$sql = "UPDATE ".$this->db_usernames." SET verified = 0 WHERE user_id = '$user_id' AND id = '$username_id'";
			if($query->sql($sql)) {
				message()->addMessage("Email unverified");
				return array("verification_status" => "NOT_VERIFIED");
			}
		}

		//error
		message()->addMessage("Could not update verification status", array("type" => "error"));
		return false;
		
	}

	
	/**
	 * Update usernames from posted values. 
	 * 
	 * Expects $email and $username_id from $_POST.
	 * /janitor/admin/user/updateEmail/#user_id#
	 *
	 * @param array $action user_id in $action[1]
	 * 
	 * @return array|true|false Returns status code indicating whether email was updated/unchanged/already existing. Returns true if email was deleted (updated to blank). False on error.
	 */
	function updateEmail($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does action match expected
		if(count($action) == 2) {
			
			$user_id = $action[1];
			$query = new Query();
			
			// make sure type tables exist
			$query->checkDbExistence($this->db_usernames);
			
			$email = $this->getProperty("email", "value");
			$username_id = getPost("username_id"); 
			
			$verification_status = $this->getProperty("verification_status", "value");
			
			
			$current_email = $this->getUsernames(array("username_id" => $username_id))["username"];
			
			// email is posted
			if($email) {
				
				$verification_code = randomKey(8);
				
				$user = $this->getUsers(["user_id" => $user_id]);
				$nickname = $user["nickname"];
				
				
				// check if email already exists
				$sql = "SELECT id FROM $this->db_usernames WHERE username = '$email' AND user_id = $user_id".($username_id ? " AND id != $username_id" : "");
				// print $sql;
				if($query->sql($sql)) {
					message()->addMessage("Email already exists for this user", array("type" => "error"));
					$status = ["email_status" => "ALREADY_EXISTS"];
					return $status;
				}
				else {
					$sql = "SELECT id FROM $this->db_usernames WHERE username = '$email'".($username_id ? " AND id != $username_id" : "");
					// print $sql;
					if($query->sql($sql)) {
						message()->addMessage("Email is used by another user", array("type" => "error"));
						$status = ["email_status" => "ALREADY_EXISTS"];
						return $status;
					}
				}


				// email has not been set before
				if(!$current_email) {

					$sql = "INSERT INTO $this->db_usernames SET username = '$email', verification_code = '$verification_code', type = 'email', user_id = $user_id";
					$query->sql($sql);
					$username_id = $query->lastInsertId();
					// print "username_id ".$username_id;
					
					$sql = "SELECT * FROM $this->db_usernames WHERE username = '$email' AND verification_code = '$verification_code' AND user_id = $user_id";
					
					
					if($query->sql($sql)) {
						
						message()->addMessage("Email added");
						$status = [
							"email_status" => "UPDATED",
						];
					}
				}
				
				// email is changed
				else if($email != $current_email) {					

					$sql = "UPDATE $this->db_usernames SET username = '$email', verification_code = '$verification_code' WHERE id = $username_id AND type = 'email' AND user_id = $user_id";
					// print $sql."<br>";
					
					if($query->sql($sql)) {

						$sql = "DELETE FROM ".SITE_DB.".user_log_verification_links WHERE username_id = $username_id AND user_id = $user_id";
						// print $sql;

						if($query->sql($sql)) {
								
							message()->addMessage("Email updated");
							$status = ["email_status" => "UPDATED"];
						}
					}
				}	
				
				// email is NOT changed
				else if($email == $current_email) {
					
					message()->addMessage("Email unchanged");
					$status = ["email_status" => "UNCHANGED"];

				}
				
				$status["username_id"] = $username_id;
				// print $username_id;	
				
				// update verification status
				$result = $this->setVerificationStatus($username_id, $user_id, $verification_status);
				// print_r($result);
				if($result && isset($result["verification_status"])) {

					$status["verification_status"] = $result["verification_status"];
					return $status;
				}		
			}

			// email is not posted
			else if(!$email) {

				if($current_email) {
					$sql = "DELETE FROM $this->db_usernames WHERE type = 'email' AND user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
						message()->addMessage("Email deleted");
						
					}
				}		
				return true;
				
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
			$query->checkDbExistence($this->db_usernames);

			$mobile = $this->getProperty("mobile", "value");

			// check if mobile exists
			if($this->userExists(array("mobile" => $mobile, "user_id" => $user_id))) {
				message()->addMessage("Mobile already exists", array("type" => "error"));
				return false;
			}


			$current_mobile = $this->getUsernames(array("user_id" => $user_id, "type" => "mobile"));

			// mobile is sent
			if($mobile) {

				$verification_code = randomKey(8);

				// mobile has not been set before
				if(!$current_mobile) {

					$sql = "INSERT INTO $this->db_usernames SET username = '$mobile', verified = 0, verification_code = '$verification_code', type = 'mobile', user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
						message()->addMessage("Mobile added");
						return true;
					}
				}

				// mobile is changed
				else if($mobile != $current_mobile) {

					$sql = "UPDATE $this->db_usernames SET username = '$mobile', verified = 0, verification_code = '$verification_code' WHERE type = 'mobile' AND user_id = $user_id";
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
	function hasPassword($_options = false) {

		$user_id = false;
		$include_empty = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"           : $user_id             = $_value; break;
					case "include_empty"     : $include_empty       = $_value; break;
				}
			}
		}

		$query = new Query();

		if($user_id) {
			$sql = "SELECT id FROM ".$this->db_passwords." WHERE user_id = $user_id" . ($include_empty ? " AND (password != '' OR upgrade_password != '')" : "");
			if($query->sql($sql)) {
				return true;
			}
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
				$query->checkDbExistence($this->db_passwords);

				$password = password_hash($this->getProperty("password", "value"), PASSWORD_DEFAULT);
				if($this->hasPassword(["user_id" => $user_id, "include_empty" => true])) {
					$sql = "UPDATE ".$this->db_passwords." SET password = '$password' WHERE user_id = $user_id";
				}
				else {
					$sql = "INSERT INTO ".$this->db_passwords." SET user_id = $user_id, password = '$password'";
				}
				if($query->sql($sql)) {
					message()->addMessage("Password saved");
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
		$query->checkDbExistence($this->db_apitokens);

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
		$query->checkDbExistence($this->db_apitokens);

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
		$query->checkDbExistence($this->db_apitokens);

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
			$query->checkDbExistence($this->db_addresses);

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




	// MAILLISTS

	// get maillist info
	// get all maillists (list of available maillists)
	// get maillists for user
	// get state of specific maillist for specific user
	// get all subscribers to maillist
	function getMaillists($_options = false) {

		$user_id = false;
		$maillist = false;
		$maillist_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"           : $user_id             = $_value; break;
					case "maillist"        : $maillist          = $_value; break;
					case "maillist_id"     : $maillist_id       = $_value; break;
				}
			}
		}

		$query = new Query();

		if($user_id) {

			// check for specific maillist (by name) for specific user
			if($maillist) {
				$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.maillist_id, maillists.name FROM ".$this->db_maillists." as subscribers, ".UT_MAILLISTS." as maillists WHERE subscribers.user_id = $user_id AND subscribers.maillist_id = maillists.id AND maillists.maillist = '$maillist'";
				if($query->sql($sql)) {
					return $query->result(0);
				}
			}
			// check for specific maillist (by id) for specific user
			else if($maillist_id) {
				$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.maillist_id, maillists.name FROM ".$this->db_maillists." as subscribers, ".UT_MAILLISTS." as maillists WHERE subscribers.user_id = $user_id AND subscribers.maillist_id = '$maillist_id'";
				if($query->sql($sql)) {
					return $query->result(0);
				}
			}
			// get maillists for specific user
			else {
				$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.maillist_id, maillists.name FROM ".$this->db_maillists." as subscribers, ".UT_MAILLISTS." as maillists WHERE subscribers.user_id = $user_id AND subscribers.maillist_id = maillists.id";
				if($query->sql($sql)) {
					return $query->results();
				}
			}

		}

		// get active users for specific maillist_id
		else if($maillist_id) {
			$sql = "SELECT subscribers.id as id, subscribers.user_id as user_id, subscribers.maillist_id as maillist_id, maillists.name as maillist, users.nickname as nickname, usernames.username as email FROM ".$this->db_maillists." as subscribers, ".UT_MAILLISTS." as maillists, ".$this->db." as users, ".$this->db_usernames." as usernames WHERE subscribers.maillist_id = '$maillist_id' AND maillists.id = $maillist_id AND subscribers.user_id = users.id AND users.status > 0 AND usernames.type = 'email' AND usernames.user_id = users.id";
			if($query->sql($sql)) {
				return $query->results();
			}
		}
		// get list of all active maillist subscribers
		else {
			$sql = "SELECT subscribers.id as id, subscribers.user_id as user_id, subscribers.maillist_id as maillist_id, maillists.name as maillist, users.nickname as nickname, usernames.username as email FROM ".$this->db_maillists." as subscribers, ".UT_MAILLISTS." as maillists, ".$this->db." as users, ".$this->db_usernames." as usernames WHERE subscribers.user_id = users.id AND users.status > 0 AND usernames.type = 'email' AND usernames.user_id = users.id AND subscribers.maillist_id = maillists.id";
			if($query->sql($sql)) {
				return $query->results();
			}
		}

	}


	// /janitor/admin/user/addMaillist/#user_id#
	// Maillist info i $_POST
	function addMaillist($action){

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 2 && $this->validateList(array("maillist_id"))) {

			$query = new Query();
			$user_id = $action[1];

			$maillist_id = $this->getProperty("maillist_id", "value");

			// already signed up (to avoid faulty double entries)
			$sql = "SELECT id FROM $this->db_maillists WHERE user_id = $user_id AND maillist_id = '$maillist_id'";
			if(!$query->sql($sql)) {
				$sql = "INSERT INTO ".$this->db_maillists." SET user_id=$user_id, maillist_id='$maillist_id'";
				$query->sql($sql);
			}

			message()->addMessage("Subscribed to maillist");
			return true;
		}

		message()->addMessage("Could not subscribe to maillist", array("type" => "error"));
		return false;
	}

	// /janitor/admin/user/deleteMaillist/#user_id#/#maillist_id#
	function deleteMaillist($action){

		// does values validate
		if(count($action) == 3) {

			$query = new Query();
			$user_id = $action[1];
			$maillist_id = $action[2];

			$sql = "DELETE FROM $this->db_maillists WHERE user_id = $user_id AND maillist_id = '$maillist_id'";
			if($query->sql($sql)) {
				message()->addMessage("Unsubscribed from maillist");
				return true;
			}
		}

		message()->addMessage("Could not unsubscribe from maillist", array("type" => "error"));
		return false;

	}



	// READSTATES
	function getReadstates($_options = false) {

		$item_id = false;
		$user_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"     : $item_id        = $_value; break;
					case "user_id"     : $user_id        = $_value; break;

				}
			}
		}

		$query = new Query();

		if($user_id) {

			// get all readstates for user
			$sql = "SELECT * FROM ".$this->db_readstates." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				return $query->results();
			}
		}
		// Get readstate for item_id
		else if($item_id) {

			$sql = "SELECT * FROM ".$this->db_readstates." WHERE item_id = $item_id AND user_id = $user_id";
			if($query->sql($sql)) {
				return $query->results();
			}

		}
		else {

			$sql = "SELECT * FROM ".$this->db_readstates;
			if($query->sql($sql)) {
				return $query->results();
			}
		}

		return false;
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

		include_once("classes/shop/supershop.class.php");
		$SC = new SuperShop();

		// get specific subscription
		if($subscription_id !== false) {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE id = $subscription_id";
			if($query->sql($sql)) {
				$subscription = $query->result(0);

				// get subscription item
				$subscription["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
//				print_r($subscription["item"]);
				// add membership info if user_id is available
				if($user_id) {
					// is this subscription used for membership
					$subscription["membership"] = $subscription["item"]["itemtype"] == "membership" ? true : false;
				}

				// extend payment method details
				if($subscription["payment_method"]) {
					$payment_method = $subscription["payment_method"];
					$subscription["payment_method"] = $page->paymentMethods($payment_method);
				}
				// payment status
				if($subscription["order_id"]) {
					$subscription["order"] = $SC->getOrders(array("order_id" => $subscription["order_id"]));
				}

				return $subscription;
			}

		}

		// get subscription for specific user
		else if($user_id !== false) {

			// check for specific subscription for specific user
			if($item_id !== false) {
				$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id AND item_id = '$item_id' LIMIT 1";
				if($query->sql($sql)) {
					$subscription = $query->result(0);

					// get subscription item
					$subscription["item"] = $IC->getItem(array("id" => $item_id, "extend" => array("prices" => true, "subscription_method" => true)));

					// is this subscription used for membership
					$subscription["membership"] = $subscription["item"]["itemtype"] == "membership" ? true : false;

					// extend payment method details
					if($subscription["payment_method"]) {
						$payment_method = $subscription["payment_method"];
						$subscription["payment_method"] = $page->paymentMethods($payment_method);
					}

					// payment status
					if($subscription["order_id"]) {
						$subscription["order"] = $SC->getOrders(array("order_id" => $subscription["order_id"]));
					}

					return $subscription;
				}
			}
			// get all subscriptions for specific user
			else {

				$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id";
				if($query->sql($sql)) {

					$subscriptions = $query->results();
					foreach($subscriptions as $i => $subscription) {

						// get subscription item
						$subscriptions[$i]["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));

						// is this subscription used for membership
						$subscriptions[$i]["membership"] = $subscriptions[$i]["item"]["itemtype"] == "membership" ? true : false;

						// extend payment method details
						if($subscription["payment_method"]) {
							$payment_method = $subscription["payment_method"];
							$subscriptions[$i]["payment_method"] = $page->paymentMethods($payment_method);
						}
						// payment status
						if($subscriptions[$i]["order_id"]) {
							$subscriptions[$i]["order"] = $SC->getOrders(array("order_id" => $subscription["order_id"]));
						}

					}
					return $subscriptions;
				}
			}

		}


		// TODO
		// get all subscriptions for specific item
		else if($item_id != false) {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE item_id = $item_id";
//			print $sql;

			if($query->sql($sql)) {
				$subscriptions = $query->results();

				foreach($subscriptions as $i => $subscription) {
					$subscription[$i]["user"] = $this->getUsers(array("user_id" => $subscription["user_id"]));

					// get subscription item
					$subscriptions[$i]["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));

					// is subscription a membership
					$subscription[$i]["membership"] = $subscriptions[$i]["item"]["itemtype"] == "membership" ? true : false;
				}

				return $subscriptions;
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


	// add a subscription
	// will only add paid subscription if order_id is passed
	// will not add subscription if subscription already exists, but returns existing subscription instead
	# /#controller#/addSubscription
	function addSubscription($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("item_id", "user_id"))) {

			$query = new Query();
			$IC = new Items();
			include_once("classes/shop/supershop.class.php");
			$SC = new SuperShop();


			$user_id = $this->getProperty("user_id", "value");
			$item_id = $this->getProperty("item_id", "value");
			$order_id = $this->getProperty("order_id", "value");
			$payment_method = $this->getProperty("payment_method", "value");

			// safety valve
			// check if subscription already exists (somehow something went wrong)
			$subscription = $this->getSubscriptions(array("item_id" => $item_id, "user_id" => $user_id));
			if($subscription) {
				// forward request to update method
				return $this->updateSubscription(array("updateSubscription", $user_id, $subscription["id"]));
			}


			// get item prices and subscription method details to create subscription correctly
			$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true, "prices" => true)));
			if($item) {


				// order flag
				$order = false;


				// item has price
				// then we need an order_id
				if(SITE_SHOP && $item["prices"]) {

					// no order_id? - don't do anything else
					if(!$order_id) {
						return false;
					}


					// check if order_id is valid
					$order = $SC->getOrders(array("order_id" => $order_id));
					if(!$order || $order["user_id"] != $user_id) {
						return false;
					}

				}


				// does subscription expire
				$expires_at = false;

				if($item["subscription_method"] && $item["subscription_method"]["duration"]) {
					$expires_at = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"]);
				}


				$sql = "INSERT INTO ".$this->db_subscriptions." SET user_id = $user_id, item_id = $item_id";
				if($order_id) {
					$sql .= ", order_id = $order_id";
				}
				if($payment_method) {
					$sql .= ", payment_method = $payment_method";
				}
				if($expires_at) {
					$sql .= ", expires_at = '$expires_at'";
				}


//				print $sql;
				if($query->sql($sql)) {

					// get new subscription
					$subscription = $this->getSubscriptions(array("item_id" => $item_id, "user_id" => $user_id));

					// if item is membership - update membership/subscription_id information
					if($item["itemtype"] == "membership") {

						// add subscription id to post array
						$_POST["subscription_id"] = $subscription["id"];
						$_POST["user_id"] = $user_id;

						// check if membership exists
						$membership = $this->getMembers(array("user_id" => $user_id));

						// safety valve
						// create membership if it does not exist
						if(!$membership) {
							$membership = $this->addMembership(array("addMembership"));
						}
						// update existing membership
						else {
							$membership = $this->updateMembership(array("updateMembership"));
						}

						// clear post array
						unset($_POST);

					}

					// perform special action on subscribe
					$model = $IC->typeObject($item["itemtype"]);
					if(method_exists($model, "subscribed")) {
						$model->subscribed($subscription);
					}

					// add to log
					global $page;
					$page->addLog("SuperUser->addSubscription: item_id:$item_id, user_id:$user_id");


					return $subscription;
				}

			}

		}

		return false;
	}

	// /janitor/admin/user/updateSubscription/#user_id#/#subscription_id#
	// info i $_POST
	function updateSubscription($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 3) {

			$query = new Query();
			$IC = new Items();

			$user_id = $action[1];
			$subscription_id = $action[2];
			$item_id = $this->getProperty("item_id", "value");
			$order_id = $this->getProperty("order_id", "value");
			$payment_method = $this->getProperty("payment_method", "value");
			$subscription_upgrade = $this->getProperty("subscription_upgrade", "value");
			$subscription_renewal = $this->getProperty("subscription_renewal", "value");

			// get item prices and subscription method details to create subscription correctly
			$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true, "prices" => true)));
			if($item) {

				// get new subscription
				$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));
				$org_item_id = $subscription["item_id"];
				// does subscription expire
				$expires_at = false;

				if($item["subscription_method"] && $item["subscription_method"]["duration"]) {

					// if renewal
					if($subscription_renewal && $subscription["expires_at"]) {
						$expires_at = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"], $subscription["expires_at"]);
					}
					// if switch or upgrade from non-expiring membership
					else if((!$subscription_upgrade || !$subscription["expires_at"])) {
						$expires_at = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"]);
					}

					// upgrade does not change exsisting expires_at

				}


				$sql = "UPDATE ".$this->db_subscriptions." SET item_id = $item_id, modified_at=CURRENT_TIMESTAMP";
				if($order_id) {
					$sql .= ", order_id = $order_id";
				}
				if($payment_method) {
					$sql .= ", payment_method = $payment_method";
				}
				if($expires_at) {
					$sql .= ", expires_at = '$expires_at'";

					if($subscription_renewal && $subscription["expires_at"]) {
						$sql .= ", renewed_at = '" . $subscription["expires_at"]."'";
					}
					else {
						$sql .= ", renewed_at = CURRENT_TIMESTAMP";
					}

				}
				else if(!$subscription_upgrade) {
					$sql .= ", expires_at = NULL";
				}

				$sql .= " WHERE user_id = $user_id AND id = $subscription_id";


//				print $sql;
				if($query->sql($sql)) {

					// if item is membership - update membership/subscription_id information
					if($item["itemtype"] == "membership") {

						// add subscription id to post array
						$_POST["subscription_id"] = $subscription_id;

						// check if membership exists
						$membership = $this->getMembers(array("user_id" => $user_id));

						// safety valve
						// create membership if it does not exist
						if(!$membership) {
							$membership = $this->addMembership(array("addMembership"));
						}
						// update existing membership
						else {
							$membership = $this->updateMembership(array("updateMembership"));
						}

						// clear post array
						unset($_POST);

					}


					// add to log
					global $page;
					$page->addLog("SuperUser->updateSubscription: subscription_id:$subscription_id, item_id:$item_id, user_id:$user_id");



					// get new subscription
					$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));


					// perform special action on subscribe to new item
					if($item_id != $org_item_id) {
						$model = $IC->typeObject($item["itemtype"]);
						if(method_exists($model, "subscribed")) {
							$model->subscribed($subscription);
						}
					}

					return $subscription;

				}

			}

		}

		return false;
	}


	// /#controller#/deleteSubscription/#user_id#/#subscription_id#
	function deleteSubscription($action) {

		// does values validate
		if(count($action) == 3) {
			$user_id = $action[1];
			$subscription_id = $action[2];

			$query = new Query();

			// check membership dependency
			$sql = "SELECT id FROM ".$this->db_members." WHERE subscription_id = $subscription_id";
//			print $sql;
			if(!$query->sql($sql)) {

				// get item id from subscription, before deleting it
				$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));

				// perform special action on unsubscribe
				$IC = new Items();
				$unsubscribed_item = $IC->getItem(array("id" => $subscription["item_id"]));
				if($unsubscribed_item) {
					$model = $IC->typeObject($unsubscribed_item["itemtype"]);
					if(method_exists($model, "unsubscribed")) {
						$model->unsubscribed($subscription);
					}
				}

				$sql = "DELETE FROM ".$this->db_subscriptions." WHERE id = $subscription_id AND user_id = $user_id";
//				print $sql;
				if($query->sql($sql)) {

					global $page;
					$page->addLog("SuperUser->deleteSubscription: subscription_id:$subscription_id user_id:$user_id");


					message()->addMessage("Subscription deleted");
					return true;
				}
			}
		}

		message()->addMessage("Subscription could not be deleted", array("type" => "error"));
		return false;
	}


	// TODO: needed for subscription renewal very soon
	// run by cron job - run after midnight to update subscriptions
	// #controller#/renewSubscriptions
	function renewSubscriptions($action) {


		// does values validate
		if(count($action) >= 1) {

			global $page;

			$query = new Query();
			$IC = new Items();

			include_once("classes/shop/supershop.class.php");
			$SC = new SuperShop();

			// renew specific user
			if(count($action) == 2) {
				$user_id = $action[1];
				// get all user subscriptions where expires_at is now
				$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE expires_at < CURDATE() AND user_id = $user_id";
				debug($sql);
			}
			// renew for all users
			else {
				// get all user subscriptions where expires_at is now
				$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE expires_at < CURDATE()";
				debug($sql);
			}


			if($query->sql($sql)) {
				$expired_subscriptions = $query->results();

				foreach($expired_subscriptions as $subscription) {

					// get item with subscription method
					$item = $IC->getItem(["id" => $subscription["item_id"], "extend" => ["subscription_method" => true]]);

					// debug([$item]);

					// Is expiry relevant (does item still require renewal)
					if($item && $item["subscription_method"] && $item["subscription_method"]["duration"] != "*") {

						// Calculate new expiry
						$new_expiry = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"], $subscription["expires_at"]);
						$price = $SC->getPrice($item["item_id"], array("quantity" => 1));

						// add order
						$_POST["user_id"] = $subscription["user_id"];
						if($item["itemtype"] == "membership") {
							$_POST["order_comment"] = "Membership renewed (" . date("d/m/Y", strtotime($subscription["expires_at"])) ." - ". date("d/m/Y", strtotime($new_expiry)).")";
						}
						else {
							$_POST["order_comment"] = "Subscription renewed (" . date("d/m/Y", strtotime($subscription["expires_at"])) ." - ". date("d/m/Y", strtotime($new_expiry)).")";
						}
						$order = $SC->addOrder(array("addOrder"));
						unset($_POST);


						// add item to order
						// adding a membership to an order will automatically change the membership to match the new order
						$_POST["quantity"] = 1;
						$_POST["item_id"] = $item["id"];
						$_POST["item_price"] = $price["price"];
						$_POST["item_name"] = $item["name"] . ", automatic renewal (" . date("d/m/Y", strtotime($subscription["expires_at"])) ." - ". date("d/m/Y", strtotime($new_expiry)).")";
						$_POST["subscription_renewal"] = 1;
						$order = $SC->addToOrder(array("addToOrder", $order["id"]));
						unset($_POST);


						if($order) {


							// CONSIDER: if payment method is stripe and we have the stripe customer_id, then charge the order directly


							$page->addLog("SuperUser->renewSubscriptions: item_id:".$subscription["item_id"].", subscription_id:".$subscription["id"].", user_id:".$subscription["user_id"].", expires_at:".$subscription["expires_at"]);

						}
						// Failed to update subscription
						else {

							mailer()->send(array(
								"subject" => SITE_URL . " - Subscription renewal failed",
								"message" => "SuperUser->renewSubscriptions: FAILED, item_id:".$subscription["item_id"].", subscription_id:".$subscription["id"].", user_id:".$subscription["user_id"].", expires_at:".$subscription["expires_at"],
								"template" => "system"
							));


							$page->addLog("SuperUser->renewSubscriptions: FAILED, item_id:".$subscription["item_id"].", subscription_id:".$subscription["id"].", user_id:".$subscription["user_id"].", expires_at:".$subscription["expires_at"]);

						}

					}
					// expiry irrelevant (item no longer expires) - remove old expires_at timestamp
					else {

						$sql = "UPDATE ".$this->db_subscriptions." SET expires_at = NULL WHERE id = ".$subscription["id"];
						// debug($sql);
						$query->sql($sql);

					}

				}

			}
			return true;
		}

		return false;
	}



	// MEMBERS

	// get members (by user_id, member_id, item_id or all)
	function getMembers($_options = false) {
		$IC = new Items();

		$member_id = false;
		$user_id = false;
		$item_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"       : $user_id         = $_value; break;
					case "member_id"     : $member_id       = $_value; break;
					case "item_id"       : $item_id         = $_value; break;
				}
			}
		}

		$query = new Query();
		include_once("classes/shop/supershop.class.php");
		$SC = new SuperShop();


		// get membership by member_id
		if($member_id !== false) {

			// membership with subscription
			$sql = "SELECT members.id as id, subscriptions.id as subscription_id, subscriptions.item_id as item_id, subscriptions.order_id as order_id, members.user_id as user_id, members.created_at as created_at, members.modified_at as modified_at, subscriptions.renewed_at as renewed_at, subscriptions.expires_at as expires_at FROM ".$this->db_subscriptions." as subscriptions, ".$this->db_members." as members WHERE members.id = $member_id AND members.subscription_id = subscriptions.id LIMIT 1";
//			print $sql;
			if($query->sql($sql)) {

				$member = $query->result(0);
				$member["user"] = $this->getUsers(array("user_id" => $member["user_id"]));
				$member["item"] = $IC->getItem(array("id" => $member["item_id"], "extend" => array("subscription_method" => true, "prices" => true)));

				if($member["order_id"]) {
					// payment status
					$member["order"] = $SC->getOrders(array("order_id" => $member["order_id"]));
				}
				else {
					$member["order"] = false;
				}

				return $member;
			}
			// membership without subscription
			else {
				$sql = "SELECT * FROM ".$this->db_members." WHERE id = $member_id LIMIT 1";
				if($query->sql($sql)) {
					$member = $query->result(0);
					$member["user"] = $this->getUsers(array("user_id" => $member["user_id"]));
					$member["item"] = false;
					$member["order"] = false;
					$member["order_id"] = false;
					$member["item_id"] = false;
					$member["expires_at"] = false;
					$member["renewed_at"] = false;
	
					return $member;
				}
			}

		}

		// get membership by user_id
		else if($user_id !== false) {

			// membership with subscription
			$sql = "SELECT members.id as id, subscriptions.id as subscription_id, subscriptions.item_id as item_id, subscriptions.order_id as order_id, members.user_id as user_id, members.created_at as created_at, members.modified_at as modified_at, subscriptions.renewed_at as renewed_at, subscriptions.expires_at as expires_at FROM ".$this->db_subscriptions." as subscriptions, ".$this->db_members." as members WHERE members.user_id = $user_id AND members.subscription_id = subscriptions.id LIMIT 1";
//			print $sql;
			if($query->sql($sql)) {

				$member = $query->result(0);
				$member["item"] = $IC->getItem(array("id" => $member["item_id"], "extend" => array("subscription_method" => true, "prices" => true)));

				// payment status
				$member["order"] = $SC->getOrders(array("order_id" => $member["order_id"]));
				return $member;
			}
			// membership without subscription
			else {
				$sql = "SELECT * FROM ".$this->db_members." WHERE user_id = $user_id LIMIT 1";
				if($query->sql($sql)) {
					$member = $query->result(0);
					$member["item"] = false;
					$member["order"] = false;
					$member["order_id"] = false;
					$member["item_id"] = false;
					$member["expires_at"] = false;
					$member["renewed_at"] = false;
					
						
	
					return $member;
				}
			}

		}

		// get all members with specific membership
		else if($item_id !== false) {

			$sql = "SELECT members.id as id, subscriptions.id as subscription_id, subscriptions.item_id as item_id, subscriptions.order_id as order_id, members.user_id as user_id, members.created_at as created_at, members.modified_at as modified_at, subscriptions.renewed_at as renewed_at, subscriptions.expires_at as expires_at FROM ".$this->db_subscriptions." as subscriptions, ".$this->db_members." as members WHERE subscriptions.item_id = $item_id AND subscriptions.id = members.subscription_id";
//			print $sql;
			if($query->sql($sql)) {
				$members = $query->results();

				foreach($members as $i => $member) {
					$members[$i]["user"] = $this->getUsers(array("user_id" => $member["user_id"]));
					$members[$i]["item"] = $IC->getItem(array("id" => $member["item_id"], "extend" => array("subscription_method" => true, "prices" => true)));

					// payment status
					$members[$i]["order"] = $SC->getOrders(array("order_id" => $member["order_id"]));
				}

				return $members;
			}

		}

		// get list of all members
		else {


			$members = false;
			$cancelled_members = false;


			$sql = "SELECT members.id as id, subscriptions.id as subscription_id, subscriptions.item_id as item_id, subscriptions.order_id as order_id, members.user_id as user_id, members.created_at as created_at, members.modified_at as modified_at, subscriptions.renewed_at as renewed_at, subscriptions.expires_at as expires_at FROM ".$this->db_subscriptions." as subscriptions, ".$this->db_members." as members WHERE subscriptions.id = members.subscription_id";
//			print $sql;
			if($query->sql($sql)) {
				$members = $query->results();

				foreach($members as $i => $member) {
					$members[$i]["user"] = $this->getUsers(array("user_id" => $member["user_id"]));
					$members[$i]["item"] = $IC->getItem(array("id" => $member["item_id"], "extend" => array("subscription_method" => true, "prices" => true)));

					// payment status
					$members[$i]["order"] = $SC->getOrders(array("order_id" => $member["order_id"]));
				}

			}

			// also include "cancelled" members
			$sql = "SELECT members.id as id, members.user_id as user_id, members.subscription_id as subscription_id, members.created_at as created_at, members.modified_at as modified_at FROM ".$this->db_members." as members WHERE members.subscription_id IS NULL";
			if($query->sql($sql)) {
				$cancelled_members = $query->results();

				foreach($cancelled_members as $i => $cancelled_member) {
					$cancelled_members[$i]["user"] = $this->getUsers(array("user_id" => $cancelled_member["user_id"]));
					$cancelled_members[$i]["item"] = false;
					$cancelled_members[$i]["order"] = false;
					$cancelled_members[$i]["order_id"] = false;
					$cancelled_members[$i]["renewed_at"] = false;
					$cancelled_members[$i]["expires_at"] = false;
				}

			}

			if($members && $cancelled_members) {
				// append cancelled members to members array
				$members = array_merge($members, $cancelled_members);
			}
			else if($cancelled_members) {
				$members = $cancelled_members;
			}

			return $members;


		}

		return false;
	}

	// a shorthand function to get order count for UI
	function getMemberCount($_options = false) {

		// get all count of orders with status
		$item_id = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"             : $item_id              = $_value; break;
				}
			}
		}

		$query = new Query();

		if($item_id !== false) {

			$sql = "SELECT count(*) as member_count, members.id as id, subscriptions.id as subscription_id, subscriptions.item_id as item_id, subscriptions.order_id as order_id, members.user_id as user_id, members.created_at as created_at, members.modified_at as modified_at, subscriptions.renewed_at as renewed_at, subscriptions.expires_at as expires_at FROM ".$this->db_subscriptions." as subscriptions, ".$this->db_members." as members WHERE subscriptions.item_id = $item_id AND subscriptions.id = members.subscription_id";
//			print $sql;
			if($query->sql($sql)) {
				return $query->result(0, "member_count");
			}

		}
		else {

			$sql = "SELECT count(*) as member_count FROM ".$this->db_members;
			if($query->sql($sql)) {
				return $query->result(0, "member_count");
			}
		}

		return 0;
	}


	// Add membership
	# /#controller#/addMembership
	function addMembership($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("user_id"))) {

			$query = new Query();

			$user_id = $this->getProperty("user_id", "value");
			$subscription_id = $this->getProperty("subscription_id", "value");


			// safety valve
			// does user already have membership
			$membership = $this->getMembers(array("user_id" => $user_id));
			if($membership) {
				return $this->updateMembership(array("updateMembership"));
			}

			// create new membership
			$sql = "INSERT INTO ".$this->db_members." SET user_id = $user_id";

			// Add subscription id if passed
			if($subscription_id) {

				// make sure subscription is valid
				$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));
				if($subscription && $subscription["user_id"] == $user_id) {
					$sql .= ", subscription_id = $subscription_id";
				}

			}

			// creating sucess
			if($query->sql($sql)) {

				$membership = $this->getMembers(array("user_id" => $user_id));

				global $page;
				$page->addLog("SuperUser->addMembership: member_id:".$membership["id"].", user_id:$user_id");

				return $membership;
			}

		}

		return false;
	}


	// update membership
	# /#controller#/updateMembership
	function updateMembership($action) {


		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1) {


			$query = new Query();
			$user_id = $this->getProperty("user_id", "value");
			$subscription_id = $this->getProperty("subscription_id", "value");


			$sql = "UPDATE ".$this->db_members." SET modified_at = CURRENT_TIMESTAMP";

			// Add subscription id if passed
			if($subscription_id) {

				// make sure subscription is valid
				$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));
				if($subscription && $subscription["user_id"] == $user_id) {
					$sql .= ", subscription_id = $subscription_id";
				}

			}

			// Add condition
			$sql .= " WHERE user_id = $user_id";


			// creating sucess
			if($query->sql($sql)) {

				$membership = $this->getMembers(array("user_id" => $user_id));

				global $page;
				$page->addLog("SuperUser->updateMembership: member_id".$membership["id"].", user_id:$user_id, subscription_id:".($subscription_id ? $subscription_id : "N/A"));

				return $membership;
			}

		}

		message()->addMessage("Membership could not be changed", array("type" => "error"));
		return false;

	}


	// cancel membership
	// removes subscription_id from membership and deletes related subscription
	# /#controller#/cancelMembership/#user_id#/#member_id#
	function cancelMembership($action) {

		// does values validate
		if(count($action) == 3) {
			$user_id = $action[1];
			$member_id = $action[2];

			$query = new Query();
			$member = $this->getMembers(array("member_id" => $member_id));
//			print_r($member);

			if($member && $member["user_id"] == $user_id) {

				// set subscription_id to NULL - maintains member in system
				$sql = "UPDATE ".$this->db_members. " SET subscription_id = NULL, modified_at = CURRENT_TIMESTAMP WHERE id = ".$member_id;
				if($query->sql($sql)) {

					// delete subscription
					$this->deleteSubscription(array("deleteSubscription", $user_id, $member["subscription_id"]));


					global $page;
					$page->addLog("SuperUser->cancelMembership: member_id:".$member["id"]);

					message()->addMessage("Membership cancelled");
					return true;

				}

			}

		}

		message()->addMessage("Membership could not be cancelled", array("type" => "error"));
		return false;
	}




	// change membership type
	// info in $_POST

	# /#controller#/switchMembership/#user_id#
	function switchMembership($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// does values validate
		if(count($action) == 2 && $this->validateList(array("item_id"))) {

			$query = new Query();
			$IC = new Items();
			
			include_once("classes/shop/supershop.class.php");
			$SC = new SuperShop();

			$user_id = $action[1];
			$item_id = $this->getProperty("item_id", "value");

			$member = $this->getMembers(array("user_id" => $user_id));
			if($member) {

				$current_user = $this->getUser();
				$_POST["user_id"] = $user_id;
				$_POST["order_comment"] = "Membership changed by ".$current_user["nickname"];
				$order = $SC->addOrder(array("addOrder"));
				unset($_POST);


				// add item to order
				$_POST["quantity"] = 1;
				$_POST["item_id"] = $item_id;
				// adding a membership to an order will automatically change the membership
				$order = $SC->addToOrder(array("addToOrder", $order["id"]));
				unset($_POST);

				if($order) {
					return $order;
				}
			}

		}

		return false;
	}


	// change membership type
	// info in $_POST

	# /#controller#/addNewhMembership/#user_id#
	function addNewhMembership($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// does values validate
		if(count($action) == 2 && $this->validateList(array("item_id"))) {

			$query = new Query();
			$IC = new Items();
			
			include_once("classes/shop/supershop.class.php");
			$SC = new SuperShop();

			$user_id = $action[1];
			$item_id = $this->getProperty("item_id", "value");

			// $member = $this->getMembers(array("user_id" => $user_id));
			// if($member) {

				$current_user = $this->getUser();
				$_POST["user_id"] = $user_id;
				$_POST["order_comment"] = "New membership added by ".$current_user["nickname"];
				$order = $SC->addOrder(array("addOrder"));
				unset($_POST);


				// add item to order
				$_POST["quantity"] = 1;
				$_POST["item_id"] = $item_id;
				// adding a membership to an order will automatically change the membership
				$order = $SC->addToOrder(array("addToOrder", $order["id"]));
				unset($_POST);

				if($order) {
					return $order;
				}
//			}

		}

		return false;
	}


	// upgrade to higher level membership
	// add new order with custom price (new_price - current_orice)
	# /#controller#/upgradeMembership/#user_id#
	function upgradeMembership($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// does values validate
		if(count($action) == 2 && $this->validateList(array("item_id"))) {

			$query = new Query();
			$IC = new Items();

			include_once("classes/shop/supershop.class.php");
			$SC = new SuperShop();

			$user_id = $action[1];
			$item_id = $this->getProperty("item_id", "value");


			$member = $this->getMembers(array("user_id" => $user_id));
			if($member && $member["item_id"]) {


				$current_user = $this->getUser();
				$_POST["user_id"] = $user_id;
				$_POST["order_comment"] = "Membership upgraded by Admin (".$current_user["nickname"].")";
				$order = $SC->addOrder(array("addOrder"));
				unset($_POST);

				// get existing membership price
				$current_price = $SC->getPrice($member["item_id"]);

				// get new item and price
				$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true)));
				$new_price = $SC->getPrice($item_id);


				// add item to order
				$_POST["quantity"] = 1;
				$_POST["item_id"] = $item_id;
				$_POST["item_price"] = $new_price["price"] - $current_price["price"];
				$_POST["item_name"] = $item["name"] . " (Upgrade)";
				$_POST["subscription_upgrade"] = 1;


				// adding a membership to an order will automatically change the membership
				$order = $SC->addToOrder(array("addToOrder", $order["id"]));
				unset($_POST);

				if($order) {
					global $page;
					$page->addLog("SuperUser->upgradeMembership: member_id:".$member["id"].",item_id:$item_id, subscription_id:".$member["subscription_id"]);

					return true;
				}

			}

		}

		return false;
	}




	/**
	 * Get all (or a subset of) unverified usernames; 
	 *
	 * @param array $_options Optional filters
	 * 		type		string		"email"|"mobile"
	 * 		user_id		int			
	 * @return array|false query result or false
	 */
	function getUnverifiedUsernames($_options = false) {

		$type = false;
		$user_id = false;
		
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "type"				: $type					= $_value; break;
					case "user_id"			: $user_id				= $_value; break;
				}
			}
		}


		$query = new Query();
		$query->checkDbExistence(SITE_DB.".user_log_verification_links");

		$SELECT = array();
		$FROM = array();
		$LEFTJOIN = array();
		$WHERE = array();
		$GROUP_BY = "";
		$ORDER = array();
		$HAVING = "";
		$LIMIT = "";
		

		$SELECT[] = "users.id as user_id";
		$SELECT[] = "usernames.id as username_id";
		$SELECT[] = "usernames.username as username";
		$SELECT[] = "usernames.verification_code as verification_code";
		$SELECT[] = "users.nickname as nickname";
		$SELECT[] = "users.created_at as created_at";

		$SELECT[] = "MAX(reminders.reminded_at) as reminded_at";
		$SELECT[] = "COUNT(reminders.id) as total_reminders";

		$FROM[] = $this->db." as users";
		$FROM[] = $this->db_usernames." as usernames";

		$WHERE[] = "users.id = usernames.user_id";
		// $WHERE[] = "users.status = 0";
		$WHERE[] = "usernames.verified = 0";
		// $WHERE[] = "usernames.type = 'email'";

		// join with activation log
		$LEFTJOIN[] = SITE_DB.".user_log_verification_links as reminders ON usernames.id = reminders.username_id";
	

		$GROUP_BY = "usernames.id";

		$ORDER[] = "reminded_at ASC";

		if($user_id) {
			$WHERE[] = "users.id = $user_id";
			// $LIMIT = 1;
		}
		if($type) {
			$WHERE[] =	"usernames.type = '$type'";
		}


		$sql = $query->compileQuery($SELECT, $FROM, array("LEFTJOIN" => $LEFTJOIN, "WHERE" => $WHERE, "HAVING" => $HAVING, "GROUP_BY" => $GROUP_BY, "ORDER" => $ORDER, "LIMIT" => $LIMIT));


		// print $sql;
		if($query->sql($sql)) {
			// print_r($query->results()); 
			return $query->results();
		}

		return false;
	}
	
	
	// /janitor/admin/user/sendVerificationLink/#username_id#	
	/**
	 * Send verification link to username_id
	 * A specific template can be posted. Default template is signup_reminder.
	 *
	 * @param array $action username_id in $action[1]	
	 * @return array|false $verification_status with "verified", "reminded_at", and "total_reminders". False on error.
	 */
	function sendVerificationLink($action) {

		$query = new Query();
		$query->checkDbExistence(SITE_DB.".user_log_verification_links");

		$template = (getPost("template") ? : "signup_reminder");
		
		// print_r($action);
		if(count($action) == 2) {
			$username_id = $action[1];
			$username_row = $this->getUsernames(["username_id" => $username_id]);
			
			$username = $username_row["username"];
			$username_type = $username_row["type"];
			$username_verification_code = $username_row["verification_code"];
			$user_id = $username_row["user_id"];
			$user_info = $this->getUserInfo(["user_id" => $user_id]);
			
			if($username_type == "email") {
				
				// use current user as sender for this reminder
				$current_user = $this->getUser();
				if(
					mailer()->send(array(
					"from_current_user" => true,
					"values" => array(
						"FROM" => $current_user["nickname"],
						"NICKNAME" => $user_info["nickname"],
						"EMAIL" => $username,
						"VERIFICATION" => $username_verification_code,
					),
					"track_clicks" => false,
					"recipients" => $username,
					"template" => $template
					))
				) { 
					message()->addMessage("Verification link sent to ".$username);
					
					// Add to user log
					$sql = "INSERT INTO ".SITE_DB.".user_log_verification_links SET user_id = ".$user_id.", username_id = ".$username_id;
					// print $sql;
					$query->sql($sql);
					$verification_status = $this->getVerificationStatus($username_id, $user_id);
					// print_r($verification_status); exit;

					return $verification_status;
				}

				message()->addMessage("Could not send verification link to ".$username, ["type" => "error"]);
				return false;
	
			}
		}		
	}
	
	/**
	 * Send verification links to list of users
	 * 
	 * Expects a comma separated string of username_ids from $_POST
	 *
	 * @param array $action 
	 * 
	 * @return array $verification_statuses with each $verification_status containing "verified", "reminded_at", "total_reminders", and "username_id".
	 */
	function sendVerificationLinks($action) {

		$selected_username_ids = explode(",", getPost("selected_username_ids"));
		$verification_statuses = [];
		
		foreach ($selected_username_ids as $username_id) {
			$verification_status = $this->sendVerificationLink(["sendVerificationLink", $username_id]);
			$verification_status["username_id"] = $username_id;
			array_push($verification_statuses, $verification_status);
		}
		
		return $verification_statuses;

	}





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
			$query->checkDbExistence($this->db_user_groups);
			$query->checkDbExistence($this->db_access);

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

			// Check that controller is formed correctly
			// ($read_access return statement exists in the begining of the file)
			$file_as_string = file_get_contents($controller);
			if(preg_match("/if[ ]?\(isset\(\\\$read_access\)[ ]?&&[ ]?\\\$read_access\)[ ]?\{/", $file_as_string)) {

				// get controller access items
				include($controller);

				// replace local path
				$short_point = str_replace(".php", "", str_replace(LOCAL_PATH."/www", "", $controller));
				// store information
				$access["points"][$short_point] = $access_item;

			}

		}


		// get and index framework controllers
		$controllers = $fs->files(FRAMEWORK_PATH."/www", array("allow_extensions" => "php"));
//		print_r($controllers);

		foreach($controllers as $controller) {
			$access_item = array();

			// Check that controller is formed correctly
			// ($read_access return statement exists in the begining of the file)
			$file_as_string = file_get_contents($controller);
			if(preg_match("/if[ ]?\(isset\(\\\$read_access\)[ ]?&&[ ]?\\\$read_access\)[ ]?\{/", $file_as_string)) {

				// get controller access items
				include($controller);

				// replace Framework path, but add /janitor/admin because that is representative for how they are accessed
				$short_point = str_replace(".php", "", str_replace(FRAMEWORK_PATH."/www", "/janitor/admin", $controller));

				// store information
				$access["points"][$short_point] = $access_item;

			}

		}


		// get settings for specific user group id
		if($user_group_id) {

			$access["permissions"] = array();

			$query = new Query();
			// make sure type tables exist
			$query->checkDbExistence($this->db_access);

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
			$query->checkDbExistence($this->db_access);

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