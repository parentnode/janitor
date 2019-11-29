<?php
/**
* @package janitor.subscription
*/

class SuperSubscriptionCore extends Subscription {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		$this->db_members = SITE_DB.".user_members";


		parent::__construct(get_class());
		


	}

	/**
	 * Get subscriptions for specified user
	 * 
	 * Passing no parameters in $_options will return all subscriptions
	 *
	 * @param array|false $_options
	 * - user_id – get all subscriptions for specified user. If combined with item id: get specific subscription for specific user.
	 * - item_id – get all subscriptions for specified item. 
	 * - subscription_id – get subscription by subscription_id
	 * 
	 * @return array|false One or several subscription objects. False on error.
	 */
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
		include_once("classes/users/superuser.class.php");
		$UC = new SuperUser();

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
					$subscription[$i]["user"] = $UC->getUsers(array("user_id" => $subscription["user_id"]));

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


	/**
	 * Add subscription to specified user
	 * 
	 * will only add paid subscription if order_id is passed
	 * will not add subscription if subscription already exists, but returns existing subscription instead
	 * 
	 * @param array $action
	 * /#controller#/addSubscription
	 * required in $_POST: user_id, item_id 
	 * optional in $_POST: payment_method, order_id
	 * 
	 * @return array|false Subscription object. False on error.
	 */
	function addSubscription($action) {

		// get posted values to make them available for models
		$this->getPostedEntities();

		// values are valid
		if(count($action) == 1 && $this->validateList(array("item_id", "user_id"))) {
			include_once("classes/shop/supershop.class.php");
			include_once("classes/users/supermember.class.php");
			$query = new Query();
			$IC = new Items();
			$SC = new SuperShop();
			$MC = new SuperMember();

			$user_id = $this->getProperty("user_id", "value");
			$item_id = $this->getProperty("item_id", "value");
			$order_id = $this->getProperty("order_id", "value");
			$payment_method = $this->getProperty("payment_method", "value");

			// safety valve
			// check if subscription already exists (somehow something went wrong)
			$subscription = $this->getSubscriptions(["item_id" => $item_id, "user_id" => $user_id]);
			if($subscription) {
				// forward request to update method
				$_POST["item_id"] = $item_id;
				$result = $this->updateSubscription(["updateSubscription", $subscription["id"]]);
				unset($_POST);
				return $result;
			}
	
			// get item prices and subscription method details to create subscription correctly
			$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true, "prices" => true)));
			if($item && $item["subscription_method"]) {
	
	
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
	
	
				if($query->sql($sql)) {
	
					// get new subscription
					$subscription = $this->getSubscriptions(array("item_id" => $item_id, "user_id" => $user_id));

					// add to log
					global $page;
					$page->addLog("SuperUser->addSubscription: item_id:$item_id, user_id:$user_id");
	
					// perform special action on subscribe
					$model = $IC->typeObject($item["itemtype"]);
					if(method_exists($model, "subscribed")) {
						$model->subscribed($subscription);
					}
	
					return $subscription;
				}
	
			}

		}

		return false;
	}

	/**
	 * Update subscription
	 *
	 * @param array $action
	 * /janitor/admin/user/updateSubscription/#subscription_id#
	 * 
	 * optional parameters in $_POST: 
	 * – item_id (item must have a subscription_method. If passed without an order_id, it will create an orderless subscription)
	 * – expires_at
	 * – custom_price
	 * — order_id
	 * – payment_method
	 * – subscription_renewal (boolean)
	 * 
	 * @return array|false Subscription object. False on error.
	 */
	function updateSubscription($action) {

		// get posted values to make them available for models
		$this->getPostedEntities();

		// values are valid
		if(count($action) == 2) {

			include_once("classes/users/supermember.class.php");
			$MC = new SuperMember();
			$query = new Query();
			$IC = new Items();

			$subscription_id = $action[1];
			$item_id = $this->getProperty("item_id", "value");
			$order_id = $this->getProperty("order_id", "value");
			$payment_method = $this->getProperty("payment_method", "value");
			$subscription_renewal = $this->getProperty("subscription_renewal", "value");
			$expires_at = $this->getProperty("expires_at", "value");
			$custom_price = $this->getProperty("custom_price", "value");

			// get original subscription and user_id
			$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));
			$user_id = $subscription["user_id"];
			
			// get original item_id and use as fallback
			$org_item_id = $subscription["item_id"];
			if(!$item_id) {
				$item_id = $org_item_id;
			}

			
			
			// get item prices and subscription method details to create subscription correctly
			$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true, "prices" => true)));
			
			// item and user_id are valid
			if($user_id && $item && $item["subscription_method"] && $item["subscription_method"]["duration"]) {
				
				// order_id was passed but item_id was not
				if($order_id && $item_id == $org_item_id) {

					// item has no price
					if(!$item["prices"]) {
						
						// a priceless item and an order cannot be combined in a subscription
						return false;
					}

				}
				// item_id was passed but order_id was not
				elseif($item_id != $org_item_id && !$order_id) {

					// item has no price
					if(!$item["prices"]) {
						
						// empty order_id to delete existing order_id
						$order_id = 'NULL';
					}
					// item has a price
					else {

						// cannot update to paid subscription without order_id
						return false;
					}
					
				}
				
				// special handling of eternal subscriptions
				if($subscription["expires_at"] === NULL) {
					
					if($subscription_renewal || $expires_at) {
					
						// cannot renew eternal subscription
						// cannot set expiration date for eternal subscription
						return false;
					}
				}

				// expiration date for new subscription is not directly specified and must be calculated
				if(!$expires_at) {
					
					// current subscription has an expiration date
					if($subscription["expires_at"]) {
						
						// current expiration date should be kept
						if(!$subscription_renewal) {
							
							$expires_at = $subscription["expires_at"];
						}
						// current expiration date should be renewed 
						else {
							
							// calculate new expiration date, counting from current expiration date
							$expires_at = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"], $subscription["expires_at"]);
						}
					}
					// current subscription never expires
					else {
						
						// new subscription will expire at some point
						if($item["subscription_method"]["duration"] != "*") {

							// calculate new expiration date, counting from current time
							$expires_at = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"]);
						}
					}
					
				}
	
				$sql = "UPDATE ".$this->db_subscriptions." SET item_id = $item_id, modified_at=CURRENT_TIMESTAMP";
				if($order_id || $order_id === 'NULL') {
					$sql .= ", order_id = $order_id";
				}
				if($payment_method) {
					$sql .= ", payment_method = $payment_method";
				}
				if($expires_at) {
					$sql .= ", expires_at = '$expires_at'";
				}
				else {
					$sql .= ", expires_at = NULL";
				}
				if($subscription_renewal && $subscription["expires_at"]) {
					$sql .= ", renewed_at = '" . $subscription["expires_at"]."'";
				}
				else if($subscription_renewal) {
					$sql .= ", renewed_at = CURRENT_TIMESTAMP";
				}
	
				$sql .= " WHERE user_id = $user_id AND id = $subscription_id";
	
				if($query->sql($sql)) {
	
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

					// callback 'renewed' on renewal
					if($subscription_renewal) {
						$model = $IC->typeObject($item["itemtype"]);
						if(method_exists($model, "subscription_renewed")) {
							$model->subscription_renewed($subscription);
						}
					}
	
					return $subscription;
	
				}
	
			}

		}

		return false;
	}


	/**
	 * Delete subscription for specified user
	 *
	 * @param array $action
	 * /#controller#/deleteSubscription/#user_id#/#subscription_id#
	 * 
	 * @return boolean True on successful deletion. False on error.
	 */
	function deleteSubscription($action) {

		// does values validate
		if(count($action) == 3) {
			$user_id = $action[1];
			$subscription_id = $action[2];
			
			$query = new Query();

			// check membership dependency
			$sql = "SELECT id FROM ".$this->db_members." WHERE subscription_id = $subscription_id";
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


	/**
	 * Renew subscriptions for specific user or all users
	 * 
	 * Run by cron job – run after midnight to update subscriptions
	 * 
	 * @param array $action
	 * #controller#/renewSubscriptions[/#user_id#]
	 * 
	 * @return boolean True on success. False on error.
	 */
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
				// get all user's subscriptions where expires_at is now
				$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE expires_at < CURDATE() AND user_id = $user_id";
				// debug($sql);
			}
			// renew for all users
			else {
				// get all subscriptions where expires_at is now
				$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE expires_at < CURDATE()";
				// debug($sql);
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

						
						// add item to cart and then create order
						// TODO: implement custom_item_name
						// $_POST["item_name"] = $item["name"] . ", automatic renewal (" . date("d/m/Y", strtotime($subscription["expires_at"])) ." - ". date("d/m/Y", strtotime($new_expiry)).")";
						
						$cart = $SC->addToNewInternalCart($item["id"], [
							"user_id" => $subscription["user_id"], 
							"custom_name" => $item["name"] . ", automatic renewal (" . date("d/m/Y", strtotime($subscription["expires_at"])) ." - ". date("d/m/Y", strtotime($new_expiry)).")"
						]);
							
						// pass subscription_renewal flag for use in updateSubscription
						$_POST["subscription_renewal"] = 1;
						$order = $SC->newOrderFromCart(["newOrderFromCart", $cart["id"], $cart["cart_reference"]]);
						unset($_POST);
						
						if($order) {
							
							// update order comment
							if($item["itemtype"] == "membership") {
								$_POST["order_comment"] = "Membership renewed (" . date("d/m/Y", strtotime($subscription["expires_at"])) ." - ". date("d/m/Y", strtotime($new_expiry)).")";
							}
							else {
								$_POST["order_comment"] = "Subscription renewed (" . date("d/m/Y", strtotime($subscription["expires_at"])) ." - ". date("d/m/Y", strtotime($new_expiry)).")";
							}							
							$order = $SC->updateOrderComment(["updateOrderComment", $order["id"]])	;
							unset($_POST);


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

}

?>