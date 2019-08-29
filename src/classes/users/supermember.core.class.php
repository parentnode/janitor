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

	// get members (by user_id, member_id, item_id or all)
	function getMemberships($_options = false) {
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
		include_once("classes/users/superuser.class.php");
		$SC = new SuperShop();
		$UC = new SuperUser();


		// get membership by member_id
		if($member_id !== false) {

			// membership with subscription
			$sql = "SELECT members.id as id, subscriptions.id as subscription_id, subscriptions.item_id as item_id, subscriptions.order_id as order_id, members.user_id as user_id, members.created_at as created_at, members.modified_at as modified_at, subscriptions.renewed_at as renewed_at, subscriptions.expires_at as expires_at FROM ".$this->db_subscriptions." as subscriptions, ".$this->db_members." as members WHERE members.id = $member_id AND members.subscription_id = subscriptions.id LIMIT 1";
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
					$member["user"] = $UC->getUsers(array("user_id" => $member["user_id"]));
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
	 * @param array|false $_options 
	 * – user_id is mandatory
	 * – subscription_id is optional
	 * 
	 * @return array|false Membership object. False on error. 
	 */
	function addMembership($item_id, $_options = false) {
		
		include_once("classes/shop/supersubscription.class.php");
		$SuperSubscriptionClass = new SuperSubscription();
		$query = new Query();

		$user_id = false;
		$subscription_id = false;
		
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"					:	$user_id					= $_value; break;
					case "subscription_id"			:	$subscription_id			= $_value; break;
				}
			}
		}

		if($user_id) {
			
			// safety valve
			// does user already have membership
			$membership = $this->getMemberships(["user_id" => $user_id]);
			if($membership) {
				return $this->updateMembership(["updateMembership"]);
			}
		
			// create new membership
			$sql = "INSERT INTO ".$this->db_members." SET user_id = $user_id";
		
			// Add subscription id if passed
			if($subscription_id) {
		
				// make sure subscription is valid
				$subscription = $SuperSubscriptionClass->getSubscriptions(["subscription_id" => $subscription_id]);
				if($subscription && $subscription["user_id"] == $user_id) {
					$sql .= ", subscription_id = $subscription_id";
				}
		
			}
		
			// creating sucess
			if($query->sql($sql)) {
		
				$membership = $this->getMemberships(["user_id" => $user_id]);
		
				global $page;
				$page->addLog("SuperMember->addMembership: member_id:".$membership["id"].", user_id:$user_id");
		
				return $membership;
			}
		}
	

		return false;
	}

	
	// change membership type
	// info in $_POST
	# /#controller#/addNewMembership/#user_id#
	function addNewMembership($action) {

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

			// $member = $this->getMemberships(array("user_id" => $user_id));
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
			//}

		}

		return false;
	}


	// update membership
	# /#controller#/updateMembership
	function updateMembership($_options = false) {


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

				$membership = $this->getMemberships(array("user_id" => $user_id));

				global $page;
				$page->addLog("SuperMember->updateMembership: member_id".$membership["id"].", user_id:$user_id, subscription_id:".($subscription_id ? $subscription_id : "N/A"));

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
			$member = $this->getMemberships(array("member_id" => $member_id));

			if($member && $member["user_id"] == $user_id) {

				// set subscription_id to NULL - maintains member in system
				$sql = "UPDATE ".$this->db_members. " SET subscription_id = NULL, modified_at = CURRENT_TIMESTAMP WHERE id = ".$member_id;
				if($query->sql($sql)) {

					// delete subscription
					$this->deleteSubscription(array("deleteSubscription", $user_id, $member["subscription_id"]));


					global $page;
					$page->addLog("SuperMember->cancelMembership: member_id:".$member["id"]);

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
	function switchMembership($item_id, $_options = false) {

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

			$member = $this->getMemberships(array("user_id" => $user_id));
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


	// upgrade to higher level membership
	// add new order with custom price (new_price - current_orice)
	# /#controller#/upgradeMembership/#user_id#
	function upgradeMembership($item_id, $_options = false) {

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


			$member = $this->getMemberships(array("user_id" => $user_id));
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
					$page->addLog("SuperMember->upgradeMembership: member_id:".$member["id"].",item_id:$item_id, subscription_id:".$member["subscription_id"]);

					return true;
				}

			}

		}

		return false;
	}


}

?>