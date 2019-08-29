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


	// add a subscription
	// will only add paid subscription if order_id is passed
	// will not add subscription if subscription already exists, but returns existing subscription instead
	# /#controller#/addSubscription
	function addSubscription($item_id, $_options = false) {

		$user_id = false;
		$order_id = false;
		$payment_method = false;
		
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					
					case "user_id"			:	$user_id			= $_value; break;	
					case "order_id"			:	$order_id			= $_value; break;
					case "payment_method"	:	$payment_method		= $_value; break;
				}
			}
		}

		if(!$user_id) {
			return false;
		}

		$query = new Query();
		$IC = new Items();
		include_once("classes/shop/supershop.class.php");
		$SC = new SuperShop();
		include_once("classes/users/supermember.class.php");
		$MC = new SuperMember();

		// safety valve
		// check if subscription already exists (somehow something went wrong)
		$subscription = $this->getSubscriptions(["item_id" => $item_id, "user_id" => $user_id]);
		if($subscription) {
			// forward request to update method
			return $this->updateSubscription($item_id, $subscription["id"], ["user_id" => $user_id]);
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
					$membership = $MC->getMemberships(array("user_id" => $user_id));

					// safety valve
					// create membership if it does not exist
					if(!$membership) {
						$membership = $MC->addMembership(array("addMembership"));
					}
					// update existing membership
					else {
						$membership = $MC->updateMembership(array("updateMembership"));
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

		return false;
	}

	function updateSubscription($item_id, $subscription_id, $_options = false) {

		$user_id = false;
		$order_id = false;
		$payment_method = false;
		$subscription_upgrade = false;
		$subscription_renewal = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					
					case "user_id"					:	$user_id				= $_value; break;
					case "order_id"					:	$order_id				= $_value; break;
					case "payment_method"			:	$payment_method			= $_value; break;
					case "subscription_upgrade"		:	$subscription_upgrade	= $_value; break;
					case "subscription_renewal"		:	$subscription_renewal	= $_value; break;
				}
			}
		}

		$query = new Query();
		$IC = new Items();

		include_once("classes/users/supermember.class.php");
		$MC = new SuperMember();

		// get item prices and subscription method details to create subscription correctly
		$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true, "prices" => true)));
		if($item && $user_id) {

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
					$membership = $MC->getMemberships(array("user_id" => $user_id));

					// safety valve
					// create membership if it does not exist
					if(!$membership) {
						$membership = $MC->addMembership(array("addMembership"));
					}
					// update existing membership
					else {
						$membership = $MC->updateMembership(array("updateMembership"));
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

		return false;
	}


	function deleteSubscription($subscription_id, $_options = false) {

		$user_id = false;
		
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					
					case "user_id"			:	$user_id			= $_value; break;	
				}
			}
		}

		if(!$user_id) {
			message()->addMessage("No user_id. Subscription could not be deleted.", array("type" => "error"));
			return false;
		}

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

		message()->addMessage("Subscription could not be deleted", array("type" => "error"));
		return false;
	}


	// TODO: needed for subscription renewal very soon
	// run by cron job - run after midnight to update subscriptions
	// #controller#/renewSubscriptions[/#user_id#]
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
				// debug($sql);
			}
			// renew for all users
			else {
				// get all user subscriptions where expires_at is now
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

						// add order
						$_POST["user_id"] = $subscription["user_id"];
						if($item["itemtype"] == "membership") {
							$_POST["order_comment"] = "Membership renewed (" . date("d/m/Y", strtotime($subscription["expires_at"])) ." - ". date("d/m/Y", strtotime($new_expiry)).")";
						}
						else {
							$_POST["order_comment"] = "Subscription renewed (" . date("d/m/Y", strtotime($subscription["expires_at"])) ." - ". date("d/m/Y", strtotime($new_expiry)).")";
						}
						$cart = $SC->addCart(["addCart"]);
						unset($_POST);


						// add item to cart and then create order
						// adding a membership to an order will automatically change the membership to match the new order
						$_POST["quantity"] = 1;
						$_POST["item_id"] = $item["id"];
						$_POST["item_price"] = $price["price"];
						$_POST["item_name"] = $item["name"] . ", automatic renewal (" . date("d/m/Y", strtotime($subscription["expires_at"])) ." - ". date("d/m/Y", strtotime($new_expiry)).")";
						$_POST["subscription_renewal"] = 1;
						$cart = $SC->addToCart(["addToCart", $cart["cart_reference"]]);
						unset($_POST);

						$order = $SC->newOrderFromCart(["newOrderFromCart", $cart["id"], $cart["cart_reference"]]);


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


}

?>