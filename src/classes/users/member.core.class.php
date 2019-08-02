<?php
/**
* @package janitor.member	
* This file contains simple member functionality
*
* The Member class is supposed to be a minimal interface to member maintenance and the user_member table
* It is vital that this class does not expose anything but the current user's information
*
* For extended member manipulation, see SuperMember.
*
*
*
*/

class MemberCore extends Model {


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		$this->db_subscriptions = SITE_DB.".user_item_subscriptions";
		$this->db_members = SITE_DB.".user_members";


		parent::__construct(get_class());

	}


	// get membership for current user
	// includes membership item and order
	function getMembership() {

		// get current user
		$user_id = session()->value("user_id");

		$query = new Query();
		$IC = new Items();
		$SC = new Shop();


		// membership with subscription
		$sql = "SELECT members.id as id, subscriptions.id as subscription_id, subscriptions.item_id as item_id, subscriptions.order_id as order_id, members.user_id as user_id, members.created_at as created_at, members.modified_at as modified_at, subscriptions.renewed_at as renewed_at, subscriptions.expires_at as expires_at FROM ".$this->db_subscriptions." as subscriptions, ".$this->db_members." as members WHERE members.user_id = $user_id AND members.subscription_id = subscriptions.id LIMIT 1";
		if($query->sql($sql)) {
			$membership = $query->result(0);
			$membership["item"] = $IC->getItem(array("id" => $membership["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
			if($membership["order_id"]) {
				$membership["order"] = $SC->getOrders(array("order_id" => $membership["order_id"]));
			}
			else {
				$membership["order"] = false;
			}

			return $membership;
		}
		// membership without subscription
		else {
			$sql = "SELECT * FROM ".$this->db_members." WHERE user_id = $user_id LIMIT 1";
			if($query->sql($sql)) {
				$membership = $query->result(0);

				$membership["item"] = false;
				$membership["order"] = false;
				$membership["order_id"] = false;
				$membership["item_id"] = false;
				$membership["expires_at"] = false;
				$membership["renewed_at"] = false;

				return $membership;
			}
		}

		return false;
	}



	// Add membership
	function addMembership($item_id, $_options = false) {
		
		// user already has membership – cancel 
		// (should have been redirected by TypeMembership::ordered)
		if($this->getMembership()) {
			return false;
		}

		// get current user
		$user_id = session()->value("user_id");
		
		$subscription_id = false;
		
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "subscription_id"			:	$subscription_id			= $_value; break;
				}
			}
		}

		$query = new Query();

		// create new membership
		$sql = "INSERT INTO ".$this->db_members." SET user_id = $user_id";

		if($subscription_id) {
			$sql .= ", subscription_id = $subscription_id";
		}

		// membership successfully created 
		if($query->sql($sql)) {

			$membership = $this->getMembership();

			global $page;
			$page->addLog("user->addMembership: member_id:".$membership["id"].", user_id:$user_id");

			return $membership;
		}

		return false;
	}

	// Update membership
	# /#controller#/updateMembership
	function updateMembership($_options = false) {

		// get current user
		$user_id = session()->value("user_id");

		$subscription_id = false;
		
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					
					case "subscription_id"			:	$subscription_id			= $_value; break;
				}
			}
		}

		$query = new Query();
		$sql = "UPDATE ".$this->db_members." SET modified_at = CURRENT_TIMESTAMP";

		// Add subscription id if passed
		if($subscription_id) {

			$SubscriptionClass = new Subscription();

			// make sure subscription is valid
			$subscription = $SubscriptionClass->getSubscriptions(array("subscription_id" => $subscription_id));
			if($subscription) {
				$sql .= ", subscription_id = $subscription_id";
			}

		}

		// Add condition
		$sql .= " WHERE user_id = $user_id";


		// creation success
		if($query->sql($sql)) {

			$membership = $this->getMembership();

			global $page;
			$page->addLog("user->updateMembership: member_id".$membership["id"].", user_id:$user_id, subscription_id:".($subscription_id ? $subscription_id : "N/A"));

			return $membership;
		}

	}


	// cancel membership
	// removes subscription_id from membership and deletes related subscription
	function cancelMembership($member_id) {

		// get current user
		$user_id = session()->value("user_id");

		// does values validate
		$query = new Query();
		$member = $this->getMembership();
//			print_r($member);

		if($member && $member["user_id"] == $user_id) {

			// set subscription_id to NULL - maintains member in system
			$sql = "UPDATE ".$this->db_members. " SET subscription_id = NULL, modified_at = CURRENT_TIMESTAMP WHERE id = ".$member_id;
			if($query->sql($sql)) {
				$SubscriptionClass = new Subscription();

				// delete subscription
				$SubscriptionClass->deleteSubscription($member["subscription_id"]);


				global $page;
				$page->addLog("User->cancelMembership: member_id:".$member["id"]);


				// send notification email to admin
				mailer()->send(array(
					"recipients" => SHOP_ORDER_NOTIFIES,
					"subject" => SITE_URL . " - Membership cancelled ($user_id)",
					"message" => "Check out the user: " . SITE_URL . "/janitor/admin/user/" . $user_id,
					// "template" => "system"
				));


				return true;

			}

		}

		return false;
	}


	// change membership type
	// info i $_POST
	// TODO: only changes item_id reference in subscription
	// - should also calculate cost difference and create new order to pay.
	// - this requires the ability to add custom order-lines with calculated price

	# /#controller#/switchMembership
	function switchMembership($item_id, $_options = false) {

		// get current user
		$user_id = session()->value("user_id");

		$query = new Query();
		$IC = new Items();
		$SC = new Shop();

		$member = $this->getMembership();
		if($member) {

			// add item to cart
			$cart = $SC->addToNewInternalCart($item_id);

			// convert to order
			// this will call Member::updateMembership via TypeMembership::ordered 
			$order = $SC->newOrderFromCart(array("newOrderFromCart", $cart["cart_reference"]));

			if($order) {
				return $order;
			}
		}

		return false;
	}


	// TODO: Creating new custom order based on existing order, should be done by shop class
	// add new order with custom price (new_price - current_price)
	// get current order and copy info to new order, then add manual order line

	# /#controller#/upgradeMembership
	function upgradeMembership($action) {

		// get current user
		$user_id = session()->value("user_id");

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// does values validate
		if(count($action) == 1 && $this->validateList(array("item_id"))) {

			$query = new Query();
			$IC = new Items();
			$SC = new Shop();

			$item_id = $this->getProperty("item_id", "value");

			$member = $this->getMembership();
			if($member && $member["item_id"]) {


				include_once("classes/shop/supershop.class.php");
				$SC = new SuperShop();


				$_POST["user_id"] = $user_id;
				$_POST["order_comment"] = "Membership upgraded";
				$order = $SC->addOrder(array("addOrder"));
				unset($_POST);

				// get existing membership price
				$current_price = $SC->getPrice($member["item_id"]);

				// get new item and price
				$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true)));
				$new_price = $SC->getPrice($item_id);


				// add item to cart
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
					$page->addLog("User->upgradeMembership: member_id:".$member["id"].",item_id:$item_id, subscription_id:".$member["subscription_id"]);


					// send notification email to admin
					mailer()->send(array(
						"recipients" => SHOP_ORDER_NOTIFIES,
						"subject" => SITE_URL . " - Membership upgraded to ".$item["name"]." ($user_id)",
						"message" => "Check out the user: " . SITE_URL . "/janitor/admin/user/" . $user_id,
						// "template" => "system"
					));


					return true;
				}

// 				// only perform membership upgrade if it is an actual upgrade
// 				if($new_price["price"] > $current_price["price"]) {
//
// 					// find price difference
// 					$order_price["price"] = $new_price["price"] - $current_price["price"];
// 					$order_price["vat"] = $new_price["price"] * (1 - (1 / (1 + ($new_price["vatrate"]/100))));
//
//
// 					// Start creating custom difference order
//
// 					// get existing order to copy data for new order
// 					$sql = "SELECT * FROM ".$SC->db_orders." WHERE id = ".$member["order_id"]." LIMIT 1";
// 					if($query->sql($sql)) {
// 						$order = $query->result(0);
//
// 						// get new order number
// 						$order_no = $SC->getNewOrderNumber();
// 						if($order_no) {
//
// 							// create base data update sql
// 							$sql = "UPDATE ".$SC->db_orders." SET comment = 'Membership upgrade'";
//
// 							foreach($order as $key => $value) {
// //								print $key . " = " . $value . "<br>\n";
// 								// filter out order specific values
// 								if(!preg_match("/(^order_no$|^id$|status$|^comment$|ed_at$)/", $key) && $value) {
// 									$sql .= ", $key = '$value'";
// 								}
//
// 							}
//
// 							$sql .= " WHERE order_no = '$order_no'";
// //							print $sql."<br>\n";
//
// 							if($query->sql($sql)) {
//
// 								// get the new order
// 								$order = $SC->getOrders(array("order_no" => $order_no));
//
// 								// add custom order line
// 								$sql = "INSERT INTO ".$SC->db_order_items." SET order_id=".$order["id"].", item_id=$item_id, name='".$item["name"]." (Upgrade)', quantity=1, unit_price=".$order_price["price"].", unit_vat=".$order_price["vat"].", total_price=".$order_price["price"].", total_vat=".$order_price["vat"];
// //								print $sql."<br>\n";
//
// 								if($query->sql($sql)) {
//
// 									// update subscription data (item id, order_id, expires_at)
//
// 									// get current subscription
// 									$subscription = $this->getSubscriptions(array("subscription_id" => $member["subscription_id"]));
//
// 									$sql = "UPDATE ".$this->db_subscriptions. " SET item_id = $item_id, order_id = ".$order["id"];
//
// 									$expires_at = false;
// 									if($item["subscription_method"]) {
// 										$start_time = $subscription["renewed_at"] ? $subscription["renewed_at"] : $subscription["created_at"];
// 										$expires_at = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"], $start_time);
// 									}
//
// 									if($expires_at) {
// 										$sql .= ", expires_at = '$expires_at'";
// 									}
// 									else {
// 										$sql .= ", expires_at = NULL";
// 									}
//
// 									$sql .= " WHERE id = ".$member["subscription_id"];
// //									print $sql."<br>\n";
//
// 									if($query->sql($sql)) {
//
// 										global $page;
// 										$page->addLog("User->upgradeMembership: member_id:".$member["id"].",item_id:$item_id, subscription_id:".$member["subscription_id"]);
//
//
// 										return true;
// 									}
//
// 								}
//
// 							}
//
// 						}
//
// 					}
//
// 				}

			}

		}

		return false;
	}
}

?>