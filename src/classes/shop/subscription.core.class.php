<?php
/**
* @package janitor.subscription
* This file contains simple subscription functionality
*
* The Subscription class is supposed to be a minimal interface to Subscription maintenance and the subscription tables
* It is vital that this class does not expose anything but the current user's information
*
* For extended Subscription manipulator, see SuperSubscription.
*
* Only for NON-Admin creation of subscriptions, like
* - maillist signup
* - subscription to products bought by current user
*
*
*/

class SubscriptionCore extends Model {


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		
		$this->db_subscriptions = SITE_DB.".user_item_subscriptions";
		
		parent::__construct(get_class());

	}

	// get subscription info for specific subscription 
	// (can be used to check if user has subscription or not)
	// get subscription for user
	function getSubscriptions($_options = false) {

		global $page;

		// get current user
		$user_id = session()->value("user_id");
		$item_id = false;
		$subscription_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"             : $item_id               = $_value; break;
					case "subscription_id"     : $subscription_id       = $_value; break;
				}
			}
		}

		$query = new Query();
		$IC = new Items();
		$SC = new Shop();

		// check for specific subscription for current user
		if($item_id !== false) {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id AND item_id = $item_id LIMIT 1";
			if($query->sql($sql)) {
				$subscription = $query->result(0);
				$subscription["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
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
		// get subscription by subscription id
		else if($subscription_id !== false) {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id AND id = $subscription_id LIMIT 1";
			if($query->sql($sql)) {
				$subscription = $query->result(0);
				$subscription["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
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

		// get list of all subscriptions for current user
		else {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				$subscriptions = $query->results();
				foreach($subscriptions as $i => $subscription) {
					$subscriptions[$i]["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
					$subscriptions[$i]["membership"] = $subscriptions[$i]["item"]["itemtype"] == "membership" ? true : false;

					// extend payment method details
					if($subscription["payment_method"]) {
						$payment_method = $subscription["payment_method"];
						$subscriptions[$i]["payment_method"] = $page->paymentMethods($payment_method);
					}

					// payment status
					if($subscription["order_id"]) {
						$subscriptions[$i]["order"] = $SC->getOrders(array("order_id" => $subscription["order_id"]));
					}
				}
				return $subscriptions;
			}
		}

		return false;
	}


	// add a subscription
	// will only add paid subscription if order_id is passed in $_options
	// will not add subscription if subscription already exists, but returns existing subscription instead
	function addSubscription($item_id, $_options = false) {
		
		$order_id = false;
		$payment_method = false;
		
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					
					case "order_id"			:	$order_id			= $_value; break;
					case "payment_method"	:	$payment_method		= $_value; break;
				}
			}
		}
		
		// get current user
		$user_id = session()->value("user_id");
		
		$query = new Query();
		$IC = new Items();
		$SC = new Shop; 
		

		// safety valve
		// check if subscription already exists (somehow something went wrong)
		$subscription = $this->getSubscriptions(array("item_id" => $item_id));
		if($subscription) {
			// forward request to update method

			return $this->updateSubscription($item_id, $subscription["id"]);
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


				$SC = new Shop();
				// check if order_id is valid
				$order = $SC->getOrders(array("order_id" => $order_id));
				if(!$order) {
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
				$subscription = $this->getSubscriptions(array("item_id" => $item_id));

				// if item is membership - update membership/subscription_id information
				if($item["itemtype"] == "membership") {

					// add subscription id to post array
					$_POST["subscription_id"] = $subscription["id"];

					// check if membership exists
					$membership = $this->getMembership();

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
				// this must be done after membership has been updated with new subscription id
				$model = $IC->typeObject($item["itemtype"]);
				if(method_exists($model, "subscribed")) {
					$model->subscribed($subscription);
				}


				// add to log
				global $page;
				$page->addLog("user->addSubscription: item_id:$item_id, user_id:$user_id");


				return $subscription;
			}

		}
		
		// Get posted values to make them available for models
		$SC->getPostedEntities();
		
		// does values validate
		if(count($action) == 1 && $SC->validateList(array("item_id"))) {
			

		}

		return false;
	}

	function updateSubscription($item_id, $subscription_id, $_options = false) {
		$order_id = false;
		$payment_method = false;
		$subscription_upgrade = false;
		$subscription_renewal = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					
					case "order_id"					:	$order_id				= $_value; break;
					case "payment_method"			:	$payment_method			= $_value; break;
					case "subscription_upgrade"		:	$subscription_upgrade	= $_value; break;
					case "subscription_renewal"		:	$subscription_renewal	= $_value; break;
				}
			}
		}

		// get current user
		$user_id = session()->value("user_id");
		

		$SC = new Shop();
		$query = new Query();
		$IC = new Items();
		
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
				
				
				$SC = new Shop();
				// check if order_id is valid
				$order = $SC->getOrders(array("order_id" => $order_id));
				if(!$order) {
					return false;
				}
				
			}
			
			
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
			

			$sql = "UPDATE ".$this->db_subscriptions." SET modified_at = CURRENT_TIMESTAMP, item_id = $item_id";
			if($order_id) {
				$sql .= ", order_id = $order_id";
			}
			if($payment_method) {
				$sql .= ", payment_method = $payment_method";
			}
			if($expires_at) {
				$sql .= ", expires_at = '$expires_at'";

				if($subscription_renewal && $subscription["expires_at"]) {
					$sql .= ", renewed_at = " . $subscription["expires_at"];
				}
				else {
					$sql .= ", renewed_at = CURRENT_TIMESTAMP";
				}

			}
			else if(!$subscription_upgrade) {
				$sql .= ", expires_at = NULL";
			}


			$sql .= " WHERE user_id = $user_id AND id = $subscription_id";


				// print $sql;
			if($query->sql($sql)) {

				// get new subscription
				$subscription = $this->getSubscriptions(array("item_id" => $item_id));

				// if item is membership - update membership/subscription_id information
				if($item["itemtype"] == "membership") {

					// add subscription id to post array
					$_POST["subscription_id"] = $subscription["id"];

					// check if membership exists
					$membership = $this->getMembership();

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

				// perform special action on subscribe to new item
				if($item_id != $org_item_id) {
					$model = $IC->typeObject($item["itemtype"]);
					if(method_exists($model, "subscribed")) {
						$model->subscribed($subscription);
					}
				}


				// add to log
				global $page;
				$page->addLog("user->updateSubscription: item_id:$item_id, user_id:$user_id");


			}

			return $subscription;

		}

		return false;
	}


	function deleteSubscription($subscription_id, $_options = false) {

		$user_id = session()->value("user_id");

		$query = new Query();

		// check membership dependency
		$sql = "SELECT id FROM ".SITE_DB.".user_members WHERE subscription_id = $subscription_id";
		if(!$query->sql($sql)) {

			// get item id from subscription, before deleting it
			$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));


			// perform special action on unsubscribe
			// before removing subscription (because unsubscribe uses it as information source)
			$IC = new Items();
			$unsubscribed_item = $IC->getItem(array("id" => $subscription["item_id"]));
			if($unsubscribed_item) {
				$model = $IC->typeObject($unsubscribed_item["itemtype"]);
				if(method_exists($model, "unsubscribed")) {
//						$model->unsubscribed($unsubscribed_item["item_id"], $user_id);
					$model->unsubscribed($subscription);
				}
			}


			$sql = "DELETE FROM ".$this->db_subscriptions." WHERE id = $subscription_id AND user_id = $user_id";
			if($query->sql($sql)) {

				global $page;
				$page->addLog("user->deleteSubscription: $subscription_id ($user_id)");

				return true;
			}
		}

		return false;
	}


	// calculate expery date for subscription
	// TODO: enable more flexible duration "settings"
	function calculateSubscriptionExpiry($duration, $start_time = false) {
//		print "calculateSubscriptionExpiry:" . $duration;

		$expires_at = false;

		if($start_time) {
			$timestamp = strtotime($start_time);
		}
		else {
			$timestamp = time();
		}


		// annually
		if($duration == "annually") {

			$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp), date("j", $timestamp), date("Y", $timestamp)+1));
		}

		// monthly
		else if($duration == "monthly") {

			$days_of_month = date("t", $timestamp);
			$date_of_month = date("j", $timestamp);

			$days_of_next_month = date("t", mktime(0, 0, 0, date("n", $timestamp)+1, 1, date("Y", $timestamp)));
			
			// if current date doesn't exist in next month (fx. 30 or 31/01)
			// if current date is last date in month 
			// - choose last day of next month
			if($date_of_month > $days_of_next_month || $date_of_month == $days_of_month) {

				$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp)+1, $days_of_next_month, date("Y", $timestamp)));
			}
			// just use same date next month
			else {

				$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp)+1, date("j", $timestamp), date("Y", $timestamp)));
			}

		}

		// weekly
		else if($duration == "weekly") {

			$expires_at = date("Y-m-d 00:00:00", $timestamp + (7*24*60*60));
		}

		return $expires_at;
	}





}

?>