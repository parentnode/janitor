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


	public $db_subscriptions;
	public $db_members;


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		$this->db_subscriptions = SITE_DB.".user_item_subscriptions";
		$this->db_members = SITE_DB.".user_members";


		parent::__construct(get_class());

	}


	/**
	 * Get membership for current user
	 * Includes membership item and order
	 *
	 * @return array|false Membership object. False on error.
	 */
	function getMembership() {

		// get current user
		$user_id = session()->value("user_id");

		$query = new Query();
		$IC = new Items();
		$SC = new Shop();


		// membership with subscription (active)
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
		// membership without subscription (inactive)
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



	/**
	 * Add membership to current user
	 *
	 * @param integer $item_id
	 * @param integer $subscription_id
	 * @param array|false $_options – Not in use. Exists to maintain compatibility with SuperMember::addMembership. 
	 * 		
	 * @return array|false Membership object. False on error. 
	 */
	function addMembership($item_id, $subscription_id, $_options = false) {

		// user already has membership – cancel 
		// (should have been redirected by TypeMembership::ordered)
		if($this->getMembership()) {
			return false;
		}

		// get current user
		$user_id = session()->value("user_id");

		$query = new Query();

		// create new membership
		$sql = "INSERT INTO ".$this->db_members." SET user_id = $user_id, subscription_id = $subscription_id";

		// membership successfully created 
		if($query->sql($sql)) {

			$membership = $this->getMembership();

			logger()->addLog("Member->addMembership: member_id:".$membership["id"].", user_id:$user_id");

			return $membership;
		}

		return false;
	}

	/**
	 * Add new membership to current user
	 * 
	 * /#controller#/addNewMembership/
	 * item_id in $_POST
	 * 
	 * @param array $action
	 * 
	 * @return array|false Order object. False on error.
	 */
	function addNewMembership($action) {

		// get posted values to make them available for models
		$this->getPostedEntities();

		// values are valid
		if(count($action) == 1 && $this->validateList(["item_id"])) {
			
			$query = new Query();
			$IC = new Items();
			$UC = new User();
			$SC = new Shop();

			$item_id = $this->getProperty("item_id", "value");

			$cart = $SC->addToNewInternalCart($item_id);
			$cart_reference = $cart ? $cart["cart_reference"] : false;

			$current_user = $UC->getUser();
			$current_user_id = $current_user["id"];

			$order = $SC->newOrderFromCart(["newOrderFromCart", $cart_reference]);
			unset($_POST);

			if($order) {

				logger()->addLog("Member->addNewMembership: user_id:$current_user_id)");

				return $order;
			}

		}

		return false;
	}



	/**
	 * Update membership for current user
	 *
	 * @param array|false $_options
	 * – subscription_id (to be used if reactivating an inactive membership)
	 * 
	 * @return array|false Membership object. False on non-existing membership. False on error. 
	 */
	function updateMembership($_options = false) {

		// current user has membership
		if($this->getMembership()) {

			// get current user
			$user_id = session()->value("user_id");

			$subscription_id = false;
			$SubscriptionClass = new Subscription();

			$query = new Query();
			$sql = "UPDATE ".$this->db_members." SET modified_at = CURRENT_TIMESTAMP";
			
			if($_options !== false) {
				foreach($_options as $_option => $_value) {
					switch($_option) {
						case "subscription_id"			:	$subscription_id			= $_value; break;
					}
				}
			}

			if($subscription_id) {

				// make sure new subscription is valid
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

				logger()->addLog("Member->updateMembership: member_id:".$membership["id"].", user_id:$user_id, subscription_id:".($subscription_id ? $subscription_id : "N/A"));
	
				return $membership;
			}
		}

		// current user has no membership
		return false;

	}


	/**
	 * Cancel membership of current user
	 * 
	 * Removes subscription_id from membership and deletes related subscription
	 * 
	 * /#controller#/cancelMembership/#member_id#
	 *
	 * @param array $action
	 * @return boolean
	 */
	function cancelMembership($action) {

		// get current user
		$user_id = session()->value("user_id");

		// does values validate
		if(count($action) == 2) {
			$member_id = $action[1];

			$query = new Query();
			$member = $this->getMembership();
	
			if($member && $member["user_id"] == $user_id) {
	
				// set subscription_id to NULL - maintains member in system
				$sql = "UPDATE ".$this->db_members. " SET subscription_id = NULL, modified_at = CURRENT_TIMESTAMP WHERE id = ".$member_id;
				if($query->sql($sql)) {
					$SubscriptionClass = new Subscription();
	
					// delete subscription
					$SubscriptionClass->deleteSubscription(["deleteSubscription", $member["subscription_id"]]);
	
	
					logger()->addLog("Member->cancelMembership: member_id:".$member["id"]);
	
					$UC = new User();
					$user = $UC->getUser();

					// send notification email to admin
					mailer()->send(array(
						"recipients" => SHOP_ORDER_NOTIFIES,
						"subject" => SITE_URL . " - Membership cancelled ($user_id)",
						"message" => "user_id: $user_id\nnickname:".$user["nickname"]."\nemail:".$user["email"]."\n\nCheck out the user: " . SITE_URL . "/janitor/admin/user/edit/" . $user_id,
						"tracking" => false
					));
	
	
					return true;
	
				}
	
			}
		}

		return false;
	}


	
	// TODO: only changes item_id reference in subscription
	// - should also calculate cost difference and create new order to pay.
	// - this requires the ability to add custom order-lines with calculated price

	/**
	 * Switch membership for current user
	 * 
	 * /#controller#/switchMembership
	 * item_id in $_POST
	 *
	 * @param array $action
	 * 
	 * @return array|false Order object. False on error.
	 */
	function switchMembership($action) {

		// get current user
		$user_id = session()->value("user_id");

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// values are valid
		if(count($action) == 1 && $this->validateList(array("item_id"))) {

			$query = new Query();
			$IC = new Items();
			$SC = new Shop();

			$item_id = $this->getProperty("item_id", "value");
	
			$member = $this->getMembership();
			if($member) {
	
				// add item to cart
				$cart = $SC->addToNewInternalCart($item_id);
	
				// convert to order
				// this will call Member::updateMembership via TypeMembership::ordered 
				// this will call Subscription::updateSubscription or Subscription::addSubscription via TypeMembership::ordered 
				
				// pass along update_expiry flag for updateSubscription
				$_POST["switch_membership"] = true;
				$order = $SC->newOrderFromCart(array("newOrderFromCart", $cart["cart_reference"]));
	
				if($order) {

					logger()->addLog("Member->switchMembership: member_id:".$member["id"].", user_id:$user_id)");

					return $order;
				}
			}
		}


		return false;
	}


	/**
	 * Upgrade membership for current user
	 * 
	 * Adds new order with custom price (new_price - current_price)
	 * Gets existing membership order and copies info to new membership order, then adds manual order line
	 * 
	 * /#controller#/upgradeMembership
	 * item_id in $_POST
	 *
	 * @param array $action
	 * 
	 * @return boolean True on successful upgrade. False on error.
	 * 
	 * @todo The creation of a new custom order based on an existing order should be done by Shop class
	 */
	function upgradeMembership($action) {

		// get current user
		$user_id = session()->value("user_id");

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// values are valid
		if(count($action) == 1 && $this->validateList(array("item_id"))) {
			
			$query = new Query();
			$IC = new Items();
			$SC = new Shop();
			$SubscriptionClass = new Subscription();

			$item_id = $this->getProperty("item_id", "value");
		
			// current user has active membership
			$member = $this->getMembership();
			if($member && $member["subscription_id"]) {
				
				// get existing membership price
				$current_price = $SC->getPrice($member["item_id"]);
				
				// get new item and price
				$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true)));
				$new_price = $SC->getPrice($item_id);
				
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
									$subscription = $SubscriptionClass->getSubscriptions(array("subscription_id" => $member["subscription_id"]));
									
									// update subscription data (item id, order_id, expires_at)
									$sql = "UPDATE ".$SubscriptionClass->db_subscriptions. " SET item_id = $item_id, order_id = ".$order["id"].", custom_price = NULL";
	
									$expires_at = false;
									if($item["subscription_method"]) {

										// current subscription has no expiry
										if(!$subscription["expires_at"]) {

											// calculate expiry date based on current date
											$start_time = date("Y-m-d H:i", time()); 
											$expires_at = $SubscriptionClass->calculateSubscriptionExpiry($item["subscription_method"]["duration"], $start_time);
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
	
									$sql .= " WHERE id = ".$member["subscription_id"];
	
									if($query->sql($sql)) {
	
										// add callback to 'upgraded'
										if(method_exists($this, "upgraded")) {
	
											$this->upgraded($member, $item);
										}

										logger()->addLog("Member->upgradeMembership: member_id:".$member["id"].",item_id:$item_id, subscription_id:".$member["subscription_id"]);
	
	
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