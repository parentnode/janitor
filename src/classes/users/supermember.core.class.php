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
			if($query->sql($sql)) {

				$member = $query->result(0);
				$member["item"] = $IC->getItem(["id" => $member["item_id"], "extend" => ["subscription_method" => true, "prices" => true]]);

				// payment status
				$member["order"] = $SC->getOrders(["order_id" => $member["order_id"]]);
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
					$members[$i]["order"] = $SC->getOrders(["order_id" => $member["order_id"]]);
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

	
	/**
	 * Get member count
	 * 
	 * A shorthand function to get order count for UI
	 * Can return the total member count, or member count for a specific membership type.
	 *
	 * @param array|false $_options
	 * – item_id 
	 * 
	 * @return string Member count
	 */
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
		
				global $page;
				$page->addLog("SuperMember->addMembership: member_id:".$membership["id"].", user_id:$user_id");
		
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

					global $page;
					$page->addLog("Member->addNewMembership: item_id:".$item_id.", user_id:$user_id");		

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

				global $page;
				$page->addLog("SuperMember->updateMembership: member_id:".$membership["id"].", user_id:$user_id, subscription_id:".($subscription_id ? $subscription_id : "N/A"));

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
	
	
					global $page;
					$page->addLog("SuperMember->cancelMembership: member_id:".$member["id"]);
	
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

					global $page;
					$page->addLog("SuperMember->switchMembership: member_id:".$member["id"].", user_id:$user_id)");

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

										global $page;
										$page->addLog("SuperMember->upgradeMembership: member_id:".$member["id"].",item_id:$item_id, subscription_id:".$member["subscription_id"]);
	
	
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