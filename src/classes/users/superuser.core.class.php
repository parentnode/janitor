<?php
/**
* @package janitor.users
* This file contains Admin User functionality
*/

class SuperUserCore extends User {


	public $db_user_groups;
	public $db_access;


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

					logger()->addLog("User created: user_id:" . $user_id . ", created by: " . session()->value("user_id"));

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


			// Do not allow deleting guest user
			if($user_id && $user_id != 1) {

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
							logger()->addLog("SuperUser->cancel: user_id:$user_id");


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

			// Do not allow deleting guest user
			if($this->userCanBeDeleted($user_id)) {

				$sql = "DELETE FROM $this->db WHERE id = ".$user_id;
				// debug([$sql]);
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

	function userCanBeDeleted($user_id) {

		// Do not allow deleting guest user
		if($user_id && $user_id != 1) {


			$user = $this->getUsers(["user_id" => $user_id]);
			// do not attempt to get cancelled or non-existent users
			if($user) {
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
					include_once("classes/users/supermember.class.php");
					$MC = new SuperMember();
					$membership = $MC->getMembers(["user_id" => $user_id]);
				}

				$IC = new Items();

				$items = $IC->getItems(["user_id" => $user_id]);
				$comments = $IC->getComments(["user_id" => $user_id]);


				if(!$orders && !$payments && !$items && !$comments && !$membership) {
					return true;
				}
			}
		}
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

		message()->addMessage("No user session found");
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
		$order = "created_at DESC";

		$username_id = false;
		$email = false;
		$mobile = false;

		$limit = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "user_group_id"	: $user_group_id	= $_value; break;
					case "user_id"			: $user_id			= $_value; break;
					case "order"			: $order			= $_value; break;

					case "username_id"		: $username_id		= $_value; break;	
					case "email"			: $email			= $_value; break;
					case "mobile"			: $mobile			= $_value; break;

					case "limit"			: $limit			= $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific user
		if($user_id) {

			$sql = "SELECT * FROM ".$this->db." WHERE id = $user_id";
			// debug(["sql", $sql]);
			if($query->sql($sql)) {
				$user = $query->result(0);
				return $user;
			}
		}

		// get users for user_group
		else if($user_group_id) {

			$sql = "SELECT * FROM ".$this->db." WHERE user_group_id = $user_group_id ORDER BY $order".($limit ? " LIMIT $limit" : "");
			// debug(["sql", $sql]);
			if($query->sql($sql)) {
				$users = $query->results();
				return $users;
			}
		}

		// get user associated with specific username_id
		else if($username_id) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE id = $username_id";
			// debug(["sql", $sql]);
			if($query->sql($sql)) {
				$user = $query->result(0);
				return $user;			}
		}

		// get users with email as username
		else if($email) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = 'email' AND username = '$email'";
			// debug(["sql", $sql]);
			if($query->sql($sql)) {
				return $query->results();
			}
		}
		// get users with mobile as username
		else if($mobile) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = 'mobile' AND username = '$mobile'";
			// debug(["sql", $sql]);
			if($query->sql($sql)) {
				return $query->results();
			}
		}

		// return all users
		else if(!isset($_options["user_id"]) && !isset($_options["user_group_id"]) && !isset($_options["email"]) && !isset($_options["mobile"])) {
			// Exclude Guest user for all-users list
			$sql = "SELECT * FROM ".$this->db." WHERE id != 1 ORDER BY $order".($limit ? " LIMIT $limit" : "");
			// debug(["sql", $sql]);
			if($query->sql($sql)) {
				 return $query->results();
			}
		}

		return false;
	}





	// SEARCH

	function search($_options = false) {
		// debug(["search", $_options]);

		// $order = "u.status DESC, u.id DESC";
		// $limit = false;

		$pattern = false;

		$query_string = "";

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "pattern"           : $pattern            = $_value; break;

					case "query"             : $query_string       = $_value; break;

				}
			}
		}

		$query = new Query();

		$sql = "SELECT u.id, u.nickname, u.last_login_at, u.status, u.modified_at, u.created_at, u.user_group_id FROM ".$this->db." AS u LEFT JOIN ".$this->db_usernames." AS un ON u.id = un.user_id WHERE u.id != 1";

		if($query_string) {
			$sql .= " AND (u.nickname LIKE '%".$query_string."%'";
				$sql .= " OR u.firstname LIKE '%".$query_string."%'";
				$sql .= " OR u.lastname LIKE '%".$query_string."%'";
				$sql .= " OR un.username LIKE '%".$query_string."%'";
			$sql .= ")";
		}

		if($pattern && isset($pattern["user_group_id"]) && $pattern["user_group_id"]) {
			$sql .= " AND user_group_id = ".$pattern["user_group_id"];
		}

		$sql .= " GROUP BY u.id";

		if($pattern && isset($pattern["order"]) && $pattern["order"]) {
			$sql .= " ORDER BY ".$pattern["order"];
		}
		// Default order
		else {
			$sql .= " ORDER BY u.created_at DESC";
		}
		if($pattern && isset($pattern["limit"]) && $pattern["limit"]) {
		// if($limit) {
			$sql .= " LIMIT ".$pattern["limit"];
		}

		// debug(["sql", $sql]);
		$query->sql($sql);
		$results = $query->results();


		// print_r($results);
		return $results;

	}




	// PAGINATION STUFF


	/**
	* Get next user(s)
	*
	* Can receive users array to use for finding next user(s) 
	* or receive query syntax to perform getItems request on it own
	*
	* @param $user_id user_id to get next from
	*/
	function getNext($user_id, $_options = false) {

		$users = false;
		$limit = 1;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "users"   : $users    = $_value; break;
					case "limit"   : $limit    = $_value; break;

				}
			}
		}

		// debug(["getNext", $user_id, $_options]);


		// filtering variables
		$next_users = array();
		$user_found = false;
		$counted = 0;

		// loop through all users, looking for starting point
		for($i = 0; $i < count($users); $i++) {

			// wait until we find starting point
			if($user_found) {

				// keep an eye on counter
				$counted++;

				// add to next scope
				$next_users[] = $users[$i];

				// end when enough users have been collected
				if($counted == $limit) {
					break;
				}
			}

			// found starting point
			else if($user_id === $users[$i]["id"]) {
				$user_found = true;
			}
		}


		// return set of next users
		return $next_users;
	}

	/**
	* Get previous user(s)
	*
	* Can receive users array to use for finding previous user(s) 
	* or receive query syntax to perform getItems request on it own
	* TODO: This implementation is far from performance optimized, but works - consider alternate implementations
	*/
	function getPrev($user_id, $_options = false) {

		$users = false;
		$limit = 1;

		// Other getItems patters properties may also be passed

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "users"   : $users    = $_value; break;
					case "limit"   : $limit    = $_value; break;
				}
			}
		}


		// filtering variables
		$prev_users = array();
		$user_found = false;
		$counted = 0;
		// loop backwards through all users, looking for starting point
		for($i = count($users)-1; $i >= 0; $i--) {

			// wait until we find starting point
			if($user_found) {

				// keep an eye on counter
				$counted++;

				// add to beginning of prev scope
				array_unshift($prev_users, $users[$i]);

				// end when enough users have been collected
				if($counted == $limit) {
					break;
				}
			}

			// found starting point
			else if($user_id === $users[$i]["id"]) {
				$user_found = true;
			}
		}


		// return set of prev users
		return $prev_users;
	}


	/**
	 * Paginate a list of users
	 * 
	 * Splits a list of users into smaller fragments and returns information required to create meaningful pagination
	 *
	 * @param array $_options
	 * * pattern – array of options to be sent to Item::getItems, which returns the users to be paginated
	 * * limit – maximal number of users per page. Default: 5.
	 * * sindex – if passed without the direction parameter, the pagination will start with the associated user
	 * * direction – can be passed in combination with the sindex parameter
	 * * * "next" – pagination will start with the user that comes *after* the user with the specified sindex. 
	 * * * "prev" – pagination will show the users that come immediately *before* the user with the specified sindex.
	 * 
	 * 
	 * @return array
	 * * range_users (list of users in specified range)
	 * * next users
	 * * previous users
	 * * first id in range
	 * * last id in range
	 * * first s_index in range
	 * * last s_index in range
	 */
	function paginate($_options) {

		// Items selected for this pagination range
		$range_users = false;


		// Start range_users from page – Default false
		$page = false;

		// Search and extend pattern for range_users / pagination
		$pattern = false;

		// Search query
		$query_string = false;

		// Limit for range_users - Default 5
		$limit = 20;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "pattern"              : $pattern         = $_value; break;

					case "limit"                : $limit           = $_value; break;
					case "page"                 : $page            = $_value; break;

					case "query"                : $query_string    = $_value; break;
				}
			}
		}


		// get all users sorted as base for pagination
		$users = $this->search(["query" => $query_string, "pattern" => $pattern]);


		// if there is no user_id or page to start from beginning
		// Select first N posts
		if(!$page) {

			// simply add limit to users query
			$pattern["limit"] = $limit;
			$range_users = $this->search(["query" => $query_string, "pattern" => $pattern]);

		}
		// Starting point exists
		else {

			$start_index = ($page-1) * $limit;
			if(count($users) >= $start_index) {

				// Find user_id of first element of page 
				$index_user = $users[$start_index];

				// Include index user (specified by passed sindex or user_id)
				// Reduce limit to make room
				// (for page based pagination it doesn't make sense to exclude user)
				$range_users = $this->getNext($index_user["id"], ["users" => $users, "limit" => $limit-1]);
				array_unshift($range_users, $index_user);

			}

		}


		// Page information
		$total_count = count($users);

		// Include page count and current page number
		$page_count = intval(ceil($total_count / $limit));
		$current_page = false;

		$first_id = false;
		// $first_sindex = false;
		$last_id = false;
		// $last_sindex = false;
		$prev = false;
		$next = false;


		if($range_users) {

			if(isset($range_users[0])) {

				$first_id = $range_users[0]["id"];
				// $first_sindex = $range_users[0]["sindex"];

				$prev = $this->getPrev($first_id, ["users" => $users, "limit" => 1]);
				if($prev) {
					$prev = $prev[0];
				}

				// If there is a first id, then there must be a last id (which might be the same, though)
				$last_id = $range_users[count($range_users)-1]["id"];
				// $last_sindex = $range_users[count($range_users)-1]["sindex"];

				$next = $this->getNext($last_id, ["users" => $users, "limit" => 1]);
				if($next) {
					$next = $next[0];
				}

				// Locate first_id in page stack
				$current_position = arrayKeyValue($users, "id", $first_id);
				$current_page = intval(floor($current_position / $limit)+1);

			}

		}


		// return all pagination info
		// range_users = list of users in specified range
		// next user
		// previous user
		// first id in range
		// last id in range
		return array("range_users" => $range_users, "next" => $next, "prev" => $prev, "first_id" => $first_id, "last_id" => $last_id, "total" => $total_count, "page_count" => $page_count, "current_page" => $current_page);
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
		// debug(["#", $username_id, "#"]);
		if($username_id) {
			$sql = "SELECT * FROM ".$this->db_usernames." WHERE id = $username_id";
			// debug([$sql]);
			if($query->sql($sql)) {
				return $query->result(0);
			}
		}

		else if($user_id) {

			// return first username of type (ordered by verification)
			if($type) {
				$sql = "SELECT * FROM ".$this->db_usernames." WHERE user_id = $user_id AND type = '$type' ORDER BY verified DESC";
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
			$verification_status = $this->getProperty("verification_status", "value");

			$username_id = getPost("username_id");

			// email was sent
			if($email) {

				// check if email already exists

				// On current user_id
				$sql = "SELECT id FROM $this->db_usernames WHERE username = '$email' AND user_id = $user_id".($username_id ? " AND id != $username_id" : "");
				// debug([$sql]);
				if($query->sql($sql)) {

					message()->addMessage("Email already exists for this user", array("type" => "error"));
					$status = ["email_status" => "ALREADY_EXISTS"];
					return $status;
				}

				// On other user_id
				else {
					$sql = "SELECT id FROM $this->db_usernames WHERE username = '$email'".($username_id ? " AND id != $username_id" : "");
					// debug([$sql]);
					if($query->sql($sql)) {

						message()->addMessage("Email is used by another user", array("type" => "error"));
						$status = ["email_status" => "ALREADY_EXISTS"];
						return $status;
					}
				}


				// Generate new verification code
				$verification_code = randomKey(8);



				// New username
				if(!$username_id) {

					// Insert new username
					$sql = "INSERT INTO $this->db_usernames SET username = '$email', verification_code = '$verification_code', type = 'email', user_id = $user_id";
					// debug([$sql]);
					if($query->sql($sql)){

						$username_id = $query->lastInsertId();

						message()->addMessage("Email added");
						$status = [
							"email_status" => "UPDATED",
						];

					}

				}


				// Modifying existing username
				else if($username_id) {

					$current_username = $this->getUsernames(array("username_id" => $username_id));



					// email is changed
					if($current_username && $current_username["type"] === "email" && $email != $current_username["username"]) {

						$sql = "UPDATE $this->db_usernames SET username = '$email', verification_code = '$verification_code' WHERE id = $username_id";
						// debug([$sql]);
						if($query->sql($sql)) {

							// Delete verification logs
							$sql = "DELETE FROM ".SITE_DB.".user_log_verification_links WHERE username_id = $username_id";
							// debug([$sql]);
							$query->sql($sql);

							message()->addMessage("Email updated");
							$status = ["email_status" => "UPDATED"];

						}

					}

					// email is NOT changed
					else if($current_username && $current_username["type"] === "email" && $email == $current_username["username"]) {

						message()->addMessage("Email unchanged");
						$status = ["email_status" => "UNCHANGED"];

					}

				}


				// Map username_id to response
				$status["username_id"] = $username_id;


				// update verification status
				$result = $this->setVerificationStatus($username_id, $user_id, $verification_status);
				if($result && isset($result["verification_status"])) {

					$status["verification_status"] = $result["verification_status"];
					return $status;

				}

			}
			// Email was empty, username_id was sent – delete username
			else if(!$email && $username_id) {

				$sql = "DELETE FROM $this->db_usernames WHERE id = $username_id AND user_id = $user_id AND type = 'email'";
				// debug([$sql]);
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



	// PAYMENT METHODS


	// return payment_methods for current user
	// can return all payment_methods for current user, or a specific payment_method
	function getPaymentMethods($_options = false) {

		$user_id = false;
		$payment_method_id = false;
		$user_payment_method_id = false;
		$gateway_payment_method_id = false;

		$extend = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "payment_method_id"          : $payment_method_id          = $_value; break;
					case "user_payment_method_id"     : $user_payment_method_id     = $_value; break;
					case "gateway_payment_method_id"  : $gateway_payment_method_id  = $_value; break;
					case "user_id"                    : $user_id                    = $_value; break;

					case "extend"                     : $extend                     = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific user_payment_method
		if($user_payment_method_id) {
			$sql = "SELECT * FROM ".$this->db_payment_methods." WHERE payment_method_id = $payment_method_id AND user_id = $user_id LIMIT 1";
			// debug(["sql", $sql]);

			if($query->sql($sql)) {
				$result = $query->result(0);
				// debug(["result", $result]);

				if($extend) {

					global $page;
					$payment_methods = $page->paymentMethods();

					$key = arrayKeyValue($payment_methods, "id", $result["payment_method_id"]);

					$result["name"] = $payment_methods[$key]["name"];
					$result["classname"] = $payment_methods[$key]["classname"];
					$result["description"] = $payment_methods[$key]["description"];
					$result["gateway"] = $payment_methods[$key]["gateway"];

					if($result["gateway"] && $gateway_payment_method_id) {
						$result["card"] = payments()->getPaymentMethod($user_id, $gateway_payment_method_id);
					}
					else {
						$result["card"] = false;
					}

				}

				return $result;
			}
		}

		// get specific payment_method
		else if($payment_method_id) {
			$sql = "SELECT * FROM ".$this->db_payment_methods." WHERE payment_method_id = $payment_method_id AND user_id = $user_id LIMIT 1";
			// debug([$sql]);

			if($query->sql($sql)) {
				$result = $query->result(0);

				if($extend) {
					global $page;
					$payment_methods = $page->paymentMethods();

					$key = arrayKeyValue($payment_methods, "id", $result["payment_method_id"]);

					$result["name"] = $payment_methods[$key]["name"];
					$result["classname"] = $payment_methods[$key]["classname"];
					$result["description"] = $payment_methods[$key]["description"];
					$result["gateway"] = $payment_methods[$key]["gateway"];

					if($result["gateway"]) {
						$result["cards"] = payments()->getPaymentMethods($user_id);
					}
					else {
						$result["cards"] = false;
					}

				}

				return $result;
			}
		}

		// get all payment_methods for user
		else {

			$sql = "SELECT * FROM ".$this->db_payment_methods." WHERE user_id = $user_id";
			// debug([$sql]);

			if($query->sql($sql)) {
				$results = $query->results();

				if($extend) {

					global $page;
					$payment_methods = $page->paymentMethods();

					foreach($results as $index => $result) {

						$key = arrayKeyValue($payment_methods, "id", $result["payment_method_id"]);
						$results[$index]["name"] = $payment_methods[$key]["name"];
						$results[$index]["classname"] = $payment_methods[$key]["classname"];
						$results[$index]["description"] = $payment_methods[$key]["description"];
						$results[$index]["gateway"] = $payment_methods[$key]["gateway"];


						if($results[$index]["gateway"]) {
							$results[$index]["cards"] = payments()->getPaymentMethods($user_id);
						}
						else {
							$results[$index]["cards"] = false;
						}

					}

				}
				return $results;
			}

		}
	}

	// Get payment method for subscription – or default payment method
	function getPaymentMethodForSubscription($_options = false) {

		$subscription_id = false;
		$user_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "subscription_id"          : $subscription_id          = $_value; break;
					case "user_id"                  : $user_id                  = $_value; break;
				}
			}
		}


		$gateway_payment_method = payments()->getPaymentMethodForSubscription($user_id, $subscription_id);
		if($gateway_payment_method) {

			$query = new Query();
			$sql = "SELECT * FROM ".$this->db_payment_methods." WHERE payment_method_id = ".$gateway_payment_method["payment_method_id"]." AND user_id = $user_id LIMIT 1";
			// debug(["sql", $sql]);
			if($query->sql($sql)) {
				$user_payment_method_id = $query->result(0, "id");

				return $this->getPaymentMethods([
					"user_id" => $user_id,
					"payment_method_id" => $gateway_payment_method["payment_method_id"],
					"gateway_payment_method_id" => $gateway_payment_method["gateway_payment_method_id"],
					"user_payment_method_id" => $user_payment_method_id,
					"extend" => true,
				]);

			}
		}

		return $this->getDefaultPaymentMethod($_options);

	}

	// Get default payment method (perhaps deducted)
	function getDefaultPaymentMethod($_options = false) {

		$query = new Query();

		$user_id = false;
		$user_payment_method = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"                  : $user_id                  = $_value; break;
				}
			}
		}

		$sql = "SELECT * FROM ".$this->db_payment_methods." WHERE user_id = $user_id AND default_method = 1";
		// debug([$sql]);

		if($query->sql($sql)) {
			$user_payment_method = $query->result(0);
		}
		else {
			$sql = "SELECT * FROM ".$this->db_payment_methods." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				$user_payment_method = $query->result(0);
			}
		}

		// Did we find a default payment method?
		if($user_payment_method) {
			$payment_method = $this->getPaymentMethods([
				"user_id" => $user_id,
				"payment_method_id" => $user_payment_method["payment_method_id"],
				"user_payment_method_id" => $user_payment_method["id"],
				"extend" => true,
			]);
			if($payment_method && (($payment_method["gateway"] && $payment_method["card"]) || !$payment_method["gateway"])) {
				return $payment_method;
			}
			else if($payment_method && $payment_method["gateway"]) {
				$payment_methods = $this->getPaymentMethods([
					"payment_method_id" => $payment_method["payment_method_id"],
					"user_id" => $user_id,
					"extend" => true,
				]);
				if($payment_methods && $payment_methods["cards"]) {
					$default_card = arrayKeyValue($payment_methods["cards"], "default", 1);
					if($default_card !== false) {
						$payment_method["card"] = $payment_methods["cards"][$default_card];
					}
					else {
						$payment_method["card"] = $payment_methods["cards"][0];
					}
					
				}
				return $payment_method;

			}
		}


		return false;
	}

	// Add user payment_method
	function addPaymentMethod($_options = false) {

		$user_id = false;
		$payment_method_id = false;

		// debug(["addPaymentMethod", $options, $user_id]);


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "payment_method_id"          : $payment_method_id         = $_value; break;
					case "user_id"                    : $user_id                   = $_value; break;
				}
			}
		}

		if($payment_method_id && $user_id) {
			$query = new Query();

			$sql = "SELECT * FROM $this->db_payment_methods WHERE payment_method_id = $payment_method_id AND user_id = $user_id";
			// debug([$sql]);
			if($query->sql($sql)) {

				$user_payment_method_id = $query->result(0, "id");
				return $user_payment_method_id;			
			}
			else {

				$sql = "INSERT INTO $this->db_payment_methods SET payment_method_id = $payment_method_id, user_id = $user_id, default_method = 1";
				// debug([$sql]);
				if($query->sql($sql)) {
					
					$user_payment_method_id = $query->lastInsertId();
					// message()->addMessage("PaymentMethod added");
					return $user_payment_method_id;
				}
			}
		}

		message()->addMessage("PaymentMethod could not be added", ["type" => "error"]);
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
					case "user_id"         : $user_id           = $_value; break;
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

		return [];
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
		$SELECT[] = "usernames.type as type";
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

			if($verification_status) {

				$verification_status["username_id"] = $username_id;
				array_push($verification_statuses, $verification_status);

			}

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