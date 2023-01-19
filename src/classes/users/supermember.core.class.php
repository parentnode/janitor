<?php
/**
* @package janitor.member
*/

class SuperMemberCore extends Member {

	/**
	*
	*/
	function __construct() {

		parent::__construct(get_class());

	}

	/**
	 * Get members (by user_id, member_id, item_id or all)
	 *
	 * Passing no parameters in $_options will return all members, including cancelled members
	 * Passing 'only_active_members' will exclude cancelled members (when getting multiple members)
	 * 
	 * @param array|false $_options
	 * * user_id – get member object for user_id
	 * * member_id – get specific member object
	 * * item_id – get all members with specific membership
	 * 
	 * @return array|false One or several membership objects. False on error.
	 */
	function getMembers($_options = false) {
		$IC = new Items();

		$member_id = false;
		$user_id = false;
		$item_id = false;
		$only_active_members = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"                   : $user_id                     = $_value; break;
					case "member_id"                 : $member_id                   = $_value; break;
					case "item_id"                   : $item_id                     = $_value; break;
					case "only_active_members"       : $only_active_members         = $_value; break;
				}
			}
		}

		$query = new Query();
		include_once("classes/shop/supershop.class.php");
		include_once("classes/users/superuser.class.php");
		$SC = new SuperShop();
		$UC = new SuperUser();


		// get membership by member_id
		if($member_id !== false) {

			// membership with subscription
			$sql = "SELECT members.id as id, subscriptions.id as subscription_id, subscriptions.item_id as item_id, subscriptions.order_id as order_id, members.user_id as user_id, members.created_at as created_at, members.modified_at as modified_at, subscriptions.renewed_at as renewed_at, subscriptions.expires_at as expires_at FROM ".$this->db_subscriptions." as subscriptions, ".$this->db_members." as members WHERE members.id = $member_id AND members.subscription_id = subscriptions.id LIMIT 1";
			// debug([$sql]);
			if($query->sql($sql)) {

				$member = $query->result(0);
				$member["user"] = $UC->getUsers(["user_id" => $member["user_id"]]);
				$member["item"] = $IC->getItem(["id" => $member["item_id"], "extend" => ["subscription_method" => true, "prices" => true]]);

				if($member["order_id"]) {
					// payment status
					$member["order"] = $SC->getOrders(["order_id" => $member["order_id"]]);
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
					$member["user"] = $UC->getUsers(["user_id" => $member["user_id"]]);
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
			// debug([$sql]);
			if($query->sql($sql)) {

				$member = $query->result(0);
				$member["item"] = $IC->getItem(["id" => $member["item_id"], "extend" => ["subscription_method" => true, "prices" => true]]);

				// payment status
				if($member["order_id"]) {
					$member["order"] = $SC->getOrders(["order_id" => $member["order_id"]]);
				}
				else {
					$member["order"] = false;
				}
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
			// get members with subscription
			if($query->sql($sql)) {
				$members = $query->results();

				foreach($members as $i => $member) {
					$members[$i]["user"] = $UC->getUsers(["user_id" => $member["user_id"]]);
					$members[$i]["item"] = $IC->getItem(["id" => $member["item_id"], "extend" => ["subscription_method" => true, "prices" => true]]);

					// payment status
					if($members[$i]["order_id"]) {
						$members[$i]["order"] = $SC->getOrders(["order_id" => $member["order_id"]]);
					}
					else {
						$members[$i]["order"] = false;
					}
				}

				return $members;
			}

		}

		// get list of all members
		else {


			$members = false;
			$cancelled_members = false;


			$sql = "SELECT members.id as id, subscriptions.id as subscription_id, subscriptions.item_id as item_id, subscriptions.order_id as order_id, members.user_id as user_id, members.created_at as created_at, members.modified_at as modified_at, subscriptions.renewed_at as renewed_at, subscriptions.expires_at as expires_at FROM ".$this->db_subscriptions." as subscriptions, ".$this->db_members." as members WHERE subscriptions.id = members.subscription_id";
			if($query->sql($sql)) {
				$members = $query->results();

				foreach($members as $i => $member) {
					$members[$i]["user"] = $UC->getUsers(["user_id" => $member["user_id"]]);
					$members[$i]["item"] = $IC->getItem(["id" => $member["item_id"], "extend" => ["subscription_method" => true, "prices" => true]]);

					// payment status
					$members[$i]["order"] = $SC->getOrders(["order_id" => $member["order_id"]]);
				}

			}

			if(!$only_active_members) {

				// also include "cancelled" members
				$sql = "SELECT members.id as id, members.user_id as user_id, members.subscription_id as subscription_id, members.created_at as created_at, members.modified_at as modified_at FROM ".$this->db_members." as members WHERE members.subscription_id IS NULL";
				if($query->sql($sql)) {
					$cancelled_members = $query->results();
	
					foreach($cancelled_members as $i => $cancelled_member) {
						$cancelled_members[$i]["user"] = $UC->getUsers(["user_id" => $cancelled_member["user_id"]]);
						$cancelled_members[$i]["item"] = false;
						$cancelled_members[$i]["item_id"] = false;
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
			}

			return $members;

		}

		return false;
	}	



	// SEARCH

	function search($_options = false) {
		// debug(["search", $_options]);


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

		include_once("classes/users/superuser.class.php");
		$UC = new SuperUser();
		include_once("classes/shop/supershop.class.php");
		$SC = new SuperShop();

		$IC = new Items();
		$membership_model = $IC->typeObject("membership");

		global $page;


		// Prepare query parts
		$SELECT = array();
		$FROM = array();
		$LEFTJOIN = array();
		$WHERE = array();
		$GROUP_BY = "";
		$HAVING = "";
		$ORDER = array();
		$LIMIT = false;

		// Add base select properties
		$SELECT[] = "m.id";
		$SELECT[] = "m.user_id";
		$SELECT[] = "m.subscription_id";

		$SELECT[] = "s.expires_at";
		$SELECT[] = "s.custom_price";

		$SELECT[] = "p.price";
		$SELECT[] = "p.currency";

		$SELECT[] = "mi.name as membership";

		$SELECT[] = "o.payment_status";

		$SELECT[] = "GROUP_CONCAT(DISTINCT un.username SEPARATOR ', ') AS usernames";

		$SELECT[] = "u.nickname";


		$FROM[] = $this->db_members." AS m";


		if(isset($pattern["item_id"])) {
			$WHERE[] = "s.item_id = ".$pattern["item_id"];
		}


		$LEFTJOIN[] = $UC->db." AS u ON m.user_id = u.id";
		$LEFTJOIN[] = $UC->db_usernames." AS un ON m.user_id = un.user_id";
		$LEFTJOIN[] = $this->db_subscriptions." AS s ON m.subscription_id = s.id";
		$LEFTJOIN[] = $SC->db_orders." AS o ON s.order_id = o.id";
		$LEFTJOIN[] = $membership_model->db." AS mi ON s.item_id = mi.item_id";
		$LEFTJOIN[] = UT_ITEMS_PRICES." AS p ON s.item_id = p.item_id AND p.currency = '".$page->currency()."'";

		if($query_string) {
			$WHERE[] = "(o.order_no LIKE '%".$query_string."%' OR u.nickname LIKE '%".$query_string."%' OR u.firstname LIKE '%".$query_string."%' OR u.lastname LIKE '%".$query_string."%' OR un.username LIKE '%".$query_string."%' OR mi.name LIKE '%".$query_string."%')";
		}

		// Use order by from pattern or default order
		$ORDER[] = isset($pattern["order"]) ? $pattern["order"] : "o.id DESC";

		// Use limit from pattern
		if(isset($pattern["limit"]) && $pattern["limit"]) {
			$LIMIT = $pattern["limit"];
		}

		$GROUP_BY = "m.id";


		$sql = $query->compileQuery($SELECT, $FROM, array("LEFTJOIN" => $LEFTJOIN, "WHERE" => $WHERE, "HAVING" => $HAVING, "GROUP_BY" => $GROUP_BY, "ORDER" => $ORDER, "LIMIT" => $LIMIT));

		// debug(["sql", $sql]);
		$query->sql($sql);
		$results = $query->results();


		// print_r($results);
		return $results;

	}




	// PAGINATION STUFF


	/**
	* Get next member(s)
	*
	* Can receive members array to use for finding next member(s) 
	* or receive query syntax to perform getItems request on it own
	*
	* @param $member_id member_id to get next from
	*/
	function getNext($member_id, $_options = false) {

		$members = false;
		$limit = 1;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "members"   : $members    = $_value; break;
					case "limit"   : $limit    = $_value; break;

				}
			}
		}

		// debug(["getNext", $member_id, $_options]);


		// filtering variables
		$next_members = array();
		$member_found = false;
		$counted = 0;

		// loop through all members, looking for starting point
		for($i = 0; $i < count($members); $i++) {

			// wait until we find starting point
			if($member_found) {

				// keep an eye on counter
				$counted++;

				// add to next scope
				$next_members[] = $members[$i];

				// end when enough members have been collected
				if($counted == $limit) {
					break;
				}
			}

			// found starting point
			else if($member_id === $members[$i]["id"]) {
				$member_found = true;
			}
		}


		// return set of next members
		return $next_members;
	}

	/**
	* Get previous member(s)
	*
	* Can receive members array to use for finding previous member(s) 
	* or receive query syntax to perform getItems request on it own
	* TODO: This implementation is far from performance optimized, but works - consider alternate implementations
	*/
	function getPrev($member_id, $_options = false) {

		$members = false;
		$limit = 1;

		// Other getItems patters properties may also be passed

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "members"  : $members    = $_value; break;
					case "limit"   : $limit    = $_value; break;
				}
			}
		}


		// filtering variables
		$prev_members = array();
		$member_found = false;
		$counted = 0;
		// loop backwards through all members, looking for starting point
		for($i = count($members)-1; $i >= 0; $i--) {

			// wait until we find starting point
			if($member_found) {

				// keep an eye on counter
				$counted++;

				// add to beginning of prev scope
				array_unshift($prev_members, $members[$i]);

				// end when enough members have been collected
				if($counted == $limit) {
					break;
				}
			}

			// found starting point
			else if($member_id === $members[$i]["id"]) {
				$member_found = true;
			}
		}


		// return set of prev members
		return $prev_members;
	}


	/**
	 * Paginate a list of members
	 * 
	 * Splits a list of members into smaller fragments and returns information required to create meaningful pagination
	 *
	 * @param array $_options
	 * * pattern – array of options to be sent to SuperShop::search, which returns the members to be paginated
	 * * limit – maximal number of members per page. Default: 5.
	 * 
	 * 
	 * @return array
	 * * range_members (list of members in specified range)
	 * * next members
	 * * previous members
	 * * first id in range
	 * * last id in range
	 */
	function paginate($_options) {

		// Items selected for this pagination range
		$range_members = false;


		// Start range_members from page – Default false
		$page = false;

		// Search and extend pattern for range_members / pagination
		$pattern = false;

		// Search query
		$query_string = false;

		// Limit for range_members - Default 5
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


		// get all members sorted as base for pagination
		$members = $this->search(["query" => $query_string, "pattern" => $pattern]);


		// if there is no user_id or page to start from beginning
		// Select first N posts
		if(!$page) {

			// simply add limit to members query
			$pattern["limit"] = $limit;
			$range_members = $this->search(["query" => $query_string, "pattern" => $pattern]);

		}
		// Starting point exists
		else {

			$start_index = ($page-1) * $limit;
			if(count($members) >= $start_index) {

				// Find user_id of first element of page 
				$index_user = $members[$start_index];

				// Include index user (specified by passed sindex or user_id)
				// Reduce limit to make room
				// (for page based pagination it doesn't make sense to exclude user)
				$range_members = $this->getNext($index_user["id"], ["members" => $members, "limit" => $limit-1]);
				array_unshift($range_members, $index_user);

			}

		}


		// Page information
		$total_count = count($members);

		// Include page count and current page number
		$page_count = intval(ceil($total_count / $limit));
		$current_page = false;

		$first_id = false;
		// $first_sindex = false;
		$last_id = false;
		// $last_sindex = false;
		$prev = false;
		$next = false;


		if($range_members) {

			if(isset($range_members[0])) {

				$first_id = $range_members[0]["id"];
				// $first_sindex = $range_members[0]["sindex"];

				$prev = $this->getPrev($first_id, ["members" => $members, "limit" => 1]);
				if($prev) {
					$prev = $prev[0];
				}

				// If there is a first id, then there must be a last id (which might be the same, though)
				$last_id = $range_members[count($range_members)-1]["id"];
				// $last_sindex = $range_members[count($range_members)-1]["sindex"];

				$next = $this->getNext($last_id, ["members" => $members, "limit" => 1]);
				if($next) {
					$next = $next[0];
				}

				// Locate first_id in page stack
				$current_position = arrayKeyValue($members, "id", $first_id);
				$current_page = intval(floor($current_position / $limit)+1);

			}

		}


		// return all pagination info
		// range_members = list of members in specified range
		// next user
		// previous user
		// first id in range
		// last id in range
		return array("range_members" => $range_members, "next" => $next, "prev" => $prev, "first_id" => $first_id, "last_id" => $last_id, "total" => $total_count, "page_count" => $page_count, "current_page" => $current_page);
	}




	/**
	 * Get member count
	 * 
	 * A shorthand function to get member count for UI
	 * Can return the total member count, or member count for a specific membership type.
	 *
	 * @param array|false $_options
	 * – item_id 
	 * 
	 * @return string Member count
	 */
	function getMemberCount($_options = false) {

		// get all count of members of item_id
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

	/**
	 * Add membership
	 *
	 * @param integer $item_id
	 * @param integer $subscription_id
	 * @param array|false $_options 
	 * – user_id (required)
	 * 
	 * @return array|false Membership object. False on error. 
	 */
	function addMembership($item_id, $subscription_id, $_options = false) {
		
		include_once("classes/shop/supersubscription.class.php");
		$SuperSubscriptionClass = new SuperSubscription();
		$query = new Query();

		$subscription = $SuperSubscriptionClass->getSubscriptions(["subscription_id" => $subscription_id]);
		$user_id = false;
		
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"					:	$user_id					= $_value; break;
				}
			}
		}
		
		// user_id was passed and subscription is valid
		if($user_id && $subscription && $subscription["user_id"] == $user_id) {
			
			// safety valve
			// does user already have membership
			$membership = $this->getMembers(["user_id" => $user_id]);
			if($membership) {
				return $this->updateMembership(["updateMembership"]);
			}
		
			// create new membership
			$sql = "INSERT INTO ".$this->db_members." SET user_id = $user_id, subscription_id = $subscription_id";
	

			// creating sucess
			if($query->sql($sql)) {
		
				$membership = $this->getMembers(["user_id" => $user_id]);
		
				logger()->addLog("SuperMember->addMembership: member_id:".$membership["id"].", user_id:$user_id");
		
				return $membership;
			}
		}
	

		return false;
	}

	
	/**
	 * Add new membership to specified user
	 * 
	 * /#controller#/addNewMembership/#user_id#
	 * info in $_POST
	 * 
	 *
	 * @param array $action
	 * 
	 * @return array|false Order object. False on error.
	 */
	function addNewMembership($action) {

		// get posted values to make them available for models
		$this->getPostedEntities();

		// values are valid
		if(count($action) == 2 && $this->validateList(["item_id"])) {
			
			include_once("classes/shop/supershop.class.php");
			include_once("classes/users/superuser.class.php");
			$query = new Query();
			$IC = new Items();
			$UC = new SuperUser();
			$SC = new SuperShop();

			$user_id = $action[1];
			$item_id = $this->getProperty("item_id", "value");

			$cart = $SC->addToNewInternalCart($item_id, ["user_id" => $user_id]);
			
			if($cart) {
				
				$cart_reference = $cart["cart_reference"];
				$cart_id = $cart["id"];
	
				$current_user = $UC->getUser();
				$_POST["order_comment"] = "New membership added by ".$current_user["nickname"];
				$order = $SC->newOrderFromCart(["newOrderFromCart", $cart_id, $cart_reference]);
				unset($_POST);
	
				if($order) {

					logger()->addLog("Member->addNewMembership: item_id:".$item_id.", user_id:$user_id");		

					return $order;
				}
			}

		}

		return false;
	}


	/**
	 * Update membership for specified user
	 *
	 * @param array|false $_options
	 * – user_id (required)
	 * – subscription_id (to be used if reactivaing an inactive membership)
	 * 
	 * @return array|false Membership object. False on non-existing membership. False on error. 
	 */
	function updateMembership($_options = false) {
		
		include_once("classes/shop/supersubscription.class.php");
		$SuperSubscriptionClass = new SuperSubscription();
		$query = new Query();
		
		$user_id = false;
		$subscription_id = false;
		
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"				:		$user_id 			= $_value; break;
					case "subscription_id"		:		$subscription_id 	= $_value; break;
				}
			}
		}
		
		$membership = $this->getMembers(["user_id" => $user_id]);
		if($membership) {
			
			$sql = "UPDATE ".$this->db_members." SET modified_at = CURRENT_TIMESTAMP";
			
			// Add subscription id if passed
			if($subscription_id) {

				// make sure subscription is valid
				$subscription = $SuperSubscriptionClass->getSubscriptions(["subscription_id" => $subscription_id, "user_id" => $user_id]);
				if($subscription && $subscription["user_id"] == $user_id) {

					$sql .= ", subscription_id = $subscription_id";

				}

			}

			// Add condition
			$sql .= " WHERE user_id = $user_id";

			// creation sucess
			if($query->sql($sql)) {

				logger()->addLog("SuperMember->updateMembership: member_id:".$membership["id"].", user_id:$user_id, subscription_id:".($subscription_id ? $subscription_id : "N/A"));

				$membership = $this->getMembers(["user_id" => $user_id]);
				return $membership;
			}
		}

		message()->addMessage("Membership could not be changed", ["type" => "error"]);
		return false;

	}


	/**
	 * Cancel membership for specified user
	 * 
	 * Removes subscription_id from membership and deletes related subscription
	 *
	 * /#controller#/cancelMembership/#user_id#/#member_id#
	 * 
	 * @param array $array
	 * 
	 * @return boolean
	 */
	function cancelMembership($action) {

		// does values validate
		if(count($action) == 3) {
			$user_id = $action[1];
			$member_id = $action[2];

			include_once("classes/shop/supersubscription.class.php");
			$SuperSubscriptionClass = new SuperSubscription();
			include_once("classes/users/superuser.class.php");
			$UC = new SuperUser();
			$query = new Query();

			$user = $UC->getUsers(["user_id" => $user_id]);
			$member = $this->getMembers(["user_id" => $user_id]);
			if($user && $member && $member["user_id"] == $user_id) {

				// set subscription_id to NULL - maintains member in system
				$sql = "UPDATE ".$this->db_members. " SET subscription_id = NULL, modified_at = CURRENT_TIMESTAMP WHERE id = ".$member_id;
				if($query->sql($sql)) {

					// delete subscription
					$SuperSubscriptionClass->deleteSubscription(["deleteSubscription", $user_id, $member["subscription_id"]]);

					logger()->addLog("SuperMember->cancelMembership: member_id:".$member["id"]);

					message()->addMessage("Membership cancelled");
					return true;
	
				}
			}
		}

		message()->addMessage("Membership could not be cancelled", ["type" => "error"]);
		return false;
	}


	/**
	 * Switch membership for specified user
	 * 
	 * /#controller#/switchMembership/#user_id#
	 * item_id in $_POST
	 *
	 * @param array $action
	 * 
	 * @return array|false Order object. False on error.
	 */
	function switchMembership($action) {

		// get posted values to make them available for models
		$this->getPostedEntities();

		

		// values are valid
		if(count($action) == 2 && $this->validateList(array("item_id"))) {

			$query = new Query();
			$IC = new Items();
			include_once("classes/shop/supershop.class.php");
			$SC = new SuperShop();
			include_once("classes/users/superuser.class.php");
			$UC = new SuperUser();

			$user_id = $action[1];
			$item_id = $this->getProperty("item_id", "value");
	
			// user exists and has membership
			$user = $UC->getUsers(["user_id" => $user_id]);
			$member = $this->getMembers(["user_id" => $user_id]);
			if($user && $member && $member["user_id"] == $user_id) {
	
				// add item to cart
				$cart = $SC->addToNewInternalCart($item_id, ["user_id" => $user_id]);
	
				// convert to order
				// this will call Member::updateMembership via TypeMembership::ordered 
				// this will call SuperSubscription::updateSubscription or SuperSubscription::addSubscription via TypeMembership::ordered 

				$_POST["switch_membership"] = true;
				$order = $SC->newOrderFromCart(array("newOrderFromCart", $cart["id"], $cart["cart_reference"]));
	
				if($order) {

					logger()->addLog("SuperMember->switchMembership: member_id:".$member["id"].", user_id:$user_id)");

					return $order;
				}
			}
		}

		return false;
	}


	/**
	 * Upgrade membership for specified user
	 * 
	 * Adds new order with custom price (new_price - current_price)
	 * Gets existing membership order and copies info to new membership order, then adds manual order line
	 * 
	 * /#controller#/upgradeMembership/#user_id#
	 * item_id in $_POST
	 *
	 * @param array $action 
	 * 
	 * @return boolean True on successful upgrade. False on error.
	 * 
	 * @todo The creation of a new custom order based on an existing order should be done by SuperShop class
	 */
	function upgradeMembership($action) {

		// get posted values to make them available for models
		$this->getPostedEntities();

		// values are valid
		if(count($action) == 2 && $this->validateList(array("item_id"))) {

			include_once("classes/shop/supershop.class.php");
			include_once("classes/shop/supersubscription.class.php");
			include_once("classes/users/superuser.class.php");
			$SC = new SuperShop();
			$UC = new SuperUser();
			$SuperSubscriptionClass = new SuperSubscription();
			$query = new Query();
			$IC = new Items();

			$user_id = $action[1];
			$item_id = $this->getProperty("item_id", "value");
	
			// user exists and has active membership
			$user = $UC->getUsers(["user_id" => $user_id]);
			$member = $this->getMembers(["user_id" => $user_id]);
			if($user && $member && $member["subscription_id"]) {
	
				// get existing membership price
				$current_price = $SC->getPrice($member["item_id"], ["user_id" => $user_id]);
	
				// get new item and price
				$item = $IC->getItem(["id" => $item_id, "extend" => ["subscription_method" => true]]);
				$new_price = $SC->getPrice($item_id, ["user_id" => $user_id]);
	
				$model = $IC->typeObject($item["itemtype"]);
	
				// only perform membership upgrade if it is an actual upgrade
				if($new_price["price"] > $current_price["price"]) {
	
					// find price difference
					$order_price["price"] = $new_price["price"] - $current_price["price"];
					$order_price["vat"] = $new_price["price"] * (1 - (1 / (1 + ($new_price["vatrate"]/100))));
	
	
					// Start creating custom difference order
	
					// get existing order to copy data for new order
					$sql = "SELECT * FROM ".$SC->db_orders." WHERE id = ".$member["order_id"]." LIMIT 1";
					if($query->sql($sql)) {
						$order = $query->result(0);
	
						// get new order number
						$order_no = $SC->getNewOrderNumber();
						if($order_no) {
	
							// create base data update sql
							$sql = "UPDATE ".$SC->db_orders." SET comment = 'Membership upgrade'";
	
							foreach($order as $key => $value) {
								//	print $key . " = " . $value . "<br>\n";
								// filter out order specific values
								if(!preg_match("/(^order_no$|^id$|status$|^comment$|ed_at$)/", $key) && $value) {
									$sql .= ", $key = '$value'";
								}
	
							}
	
							$sql .= " WHERE order_no = '$order_no'";
	
							if($query->sql($sql)) {
	
								// get the new order
								$order = $SC->getOrders(array("order_no" => $order_no));
	
								// add custom order line
								$sql = "INSERT INTO ".$SC->db_order_items." SET order_id=".$order["id"].", item_id=$item_id, name='".$item["name"]." (Upgrade)', quantity=1, unit_price=".$order_price["price"].", unit_vat=".$order_price["vat"].", total_price=".$order_price["price"].", total_vat=".$order_price["vat"];
								if($query->sql($sql)) {
	
									// get current subscription
									$subscription = $SuperSubscriptionClass->getSubscriptions(array("subscription_id" => $member["subscription_id"]));
									
									// update subscription data (item id, order_id, expires_at)
									$sql = "UPDATE ".$SuperSubscriptionClass->db_subscriptions. " SET item_id = $item_id, order_id = ".$order["id"].", custom_price = NULL";
	
									$expires_at = false;
									if($item["subscription_method"]) {
										// current subscription has no expiry
										if(!$subscription["expires_at"]) {

											// calculate expiry date based on current date
											$start_time = date("Y-m-d H:i", time()); 
											$expires_at = $SuperSubscriptionClass->calculateSubscriptionExpiry($item["subscription_method"]["duration"], $start_time);
										}
										else {

											// keep expiry date
											$expires_at = $subscription["expires_at"];
										}									
									}
	
									if($expires_at) {
										$sql .= ", expires_at = '$expires_at'";
									}
									else {
										$sql .= ", expires_at = NULL";
									}

									// Reset custom price
									$sql .= ", custom_price = NULL";

									// Add condition
									$sql .= " WHERE id = ".$member["subscription_id"];
	
									if($query->sql($sql)) {
	
										// add callback to 'upgraded'
										if(method_exists($this, "upgraded")) {
	
											$this->upgraded($member, $item);
										}

										logger()->addLog("SuperMember->upgradeMembership: member_id:".$member["id"].",item_id:$item_id, subscription_id:".$member["subscription_id"]);
	
	
										return true;
									}
								}
							}
						}
					}
				}
	
			}
		}
		

		return false;
	}


}

?>